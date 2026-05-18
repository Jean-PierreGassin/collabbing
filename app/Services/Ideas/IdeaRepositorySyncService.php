<?php

namespace App\Services\Ideas;

use App\Models\CodeRepository;
use App\Models\Idea;
use App\Models\User;
use App\Services\ThirdParty\GitHub\GitHubRepositoryClient;
use Carbon\Carbon;
use Github\Exception\RuntimeException as GitHubRuntimeException;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class IdeaRepositorySyncService
{
    public function __construct(private GitHubRepositoryClient $github) {}

    public function sync(CodeRepository $codeRepository): void
    {
        $codeRepository->loadMissing('idea.user.githubAccount');

        $idea = $codeRepository->idea;
        $owner = null;

        if ($idea instanceof Idea) {
            $owner = $idea->owner();
        }

        if (
            $codeRepository->provider !== CodeRepository::PROVIDER_GITHUB
            || $codeRepository->status !== CodeRepository::STATUS_ACTIVE
            || ! $owner instanceof User
            || ! $owner->githubToken()
            || ! $owner->githubUsername()
            || ! $codeRepository->name
        ) {
            return;
        }

        try {
            $repository = $this->github->show($owner, $codeRepository->name);
        } catch (GitHubRuntimeException $exception) {
            if ($exception->getCode() === 404) {
                $this->markMissing($codeRepository);

                return;
            }

            throw $exception;
        }

        $branch = Arr::get($repository, 'default_branch');

        if (! is_string($branch)) {
            $branch = null;
        }

        $latestCommit = $this->github->latestCommit($owner, $codeRepository->name, $branch);

        $this->recordSnapshot($codeRepository, $repository, $latestCommit);
    }

    public function recordCreated(CodeRepository $codeRepository, array $repository): void
    {
        $this->recordSnapshot($codeRepository, $repository, null);

        $this->recordEvent(
            $codeRepository,
            'repository_created',
            'Repository created on GitHub.',
            'repository_created:'.$codeRepository->name,
            now(),
            $this->repositoryPayload($repository)
        );
    }

    public function recordSnapshot(CodeRepository $codeRepository, array $repository, ?array $latestCommit): void
    {
        $wasSynced = (bool) $codeRepository->synced_at;
        $previousBranch = $codeRepository->default_branch;
        $previousIssuesCount = $codeRepository->open_issues_count;
        $previousCommitSha = $codeRepository->latest_commit_sha;
        $previousMissingAt = $codeRepository->missing_at;

        $latestCommitSha = Arr::get($latestCommit ?? [], 'sha');
        $latestCommitMessage = $this->commitMessage($latestCommit);
        $latestCommitAuthor = $this->commitAuthor($latestCommit);
        $pushedAt = $this->parseDate(Arr::get($repository, 'pushed_at'));
        $syncedAt = now();
        $fullName = Arr::get($repository, 'full_name');
        $repositoryFullName = $codeRepository->full_name;

        if (is_string($fullName)) {
            $repositoryFullName = $fullName;
        }

        $codeRepository->forceFill([
            'status' => CodeRepository::STATUS_ACTIVE,
            'provider_repository_id' => $this->repositoryProviderId($repository),
            'owner' => $this->repositoryOwner($repository, $codeRepository->owner),
            'name' => Arr::get($repository, 'name', $codeRepository->name),
            'full_name' => $repositoryFullName,
            'html_url' => Arr::get($repository, 'html_url'),
            'default_branch' => Arr::get($repository, 'default_branch'),
            'open_issues_count' => (int) Arr::get($repository, 'open_issues_count', 0),
            'stargazers_count' => (int) Arr::get($repository, 'stargazers_count', 0),
            'forks_count' => (int) Arr::get($repository, 'forks_count', 0),
            'latest_commit_sha' => $latestCommitSha,
            'latest_commit_message' => $latestCommitMessage,
            'latest_commit_author' => $latestCommitAuthor,
            'pushed_at' => $pushedAt,
            'synced_at' => $syncedAt,
            'missing_at' => null,
            'sync_due_at' => $this->nextSyncDueAt($syncedAt),
        ])->save();

        if (! $wasSynced) {
            $this->recordEvent(
                $codeRepository,
                'repository_synced',
                'Repository sync connected to GitHub.',
                'repository_synced:'.$codeRepository->name,
                $syncedAt,
                $this->repositoryPayload($repository)
            );
        }

        if ($previousMissingAt) {
            $this->recordEvent(
                $codeRepository,
                'repository_restored',
                'Repository is available on GitHub again.',
                'repository_restored:'.$codeRepository->name.':'.$syncedAt->timestamp,
                $syncedAt,
                $this->repositoryPayload($repository)
            );
        }

        if ($latestCommitSha && $latestCommitSha !== $previousCommitSha) {
            $commitSummary = 'Repository received a new commit.';

            if ($latestCommitMessage) {
                $commitSummary = 'Latest commit: '.$latestCommitMessage;
            }

            $commitOccurredAt = $this->parseDate(Arr::get($latestCommit ?? [], 'commit.committer.date'));

            if (! $commitOccurredAt) {
                $commitOccurredAt = $pushedAt;
            }

            if (! $commitOccurredAt) {
                $commitOccurredAt = $syncedAt;
            }

            $this->recordEvent(
                $codeRepository,
                'repository_commit',
                $commitSummary,
                'commit:'.$latestCommitSha,
                $commitOccurredAt,
                [
                    'sha' => $latestCommitSha,
                    'message' => $latestCommitMessage,
                    'author' => $latestCommitAuthor,
                ]
            );
        }

        if ($wasSynced && $previousBranch && $previousBranch !== $codeRepository->default_branch) {
            $this->recordEvent(
                $codeRepository,
                'repository_branch_changed',
                "Default branch changed from {$previousBranch} to {$codeRepository->default_branch}.",
                'default_branch:'.$codeRepository->default_branch.':'.$syncedAt->timestamp,
                $syncedAt,
                ['previous' => $previousBranch, 'current' => $codeRepository->default_branch]
            );
        }

        if ($wasSynced && $previousIssuesCount !== $codeRepository->open_issues_count) {
            $this->recordEvent(
                $codeRepository,
                'repository_issues_changed',
                "Open issues changed from {$previousIssuesCount} to {$codeRepository->open_issues_count}.",
                'open_issues:'.$codeRepository->open_issues_count.':'.$syncedAt->timestamp,
                $syncedAt,
                ['previous' => $previousIssuesCount, 'current' => $codeRepository->open_issues_count]
            );
        }
    }

    private function markMissing(CodeRepository $codeRepository): void
    {
        $missingAt = now();

        $codeRepository->forceFill([
            'status' => CodeRepository::STATUS_MISSING,
            'synced_at' => $missingAt,
            'missing_at' => $missingAt,
            'sync_due_at' => null,
        ])->save();

        $this->recordEvent(
            $codeRepository,
            'repository_missing',
            'Repository is no longer available on GitHub.',
            'repository_missing:'.$codeRepository->name.':'.$missingAt->toDateString(),
            $missingAt,
            ['repository_name' => $codeRepository->name]
        );
    }

    private function recordEvent(CodeRepository $codeRepository, string $type, string $summary, string $dedupeKey, Carbon $occurredAt, array $payload = []): void
    {
        $codeRepository->events()->firstOrCreate(
            ['dedupe_key' => $dedupeKey],
            [
                'type' => $type,
                'summary' => Str::limit($summary, 255, ''),
                'occurred_at' => $occurredAt,
                'payload' => $payload,
            ]
        );
    }

    public function scheduleRetry(CodeRepository $codeRepository): void
    {
        $codeRepository->forceFill([
            'sync_due_at' => $this->retrySyncDueAt(now()),
        ])->save();
    }

    private function nextSyncDueAt(Carbon $from): Carbon
    {
        return $from->copy()->addMinutes(random_int(
            (int) config('services.github.repository_sync.next_min_minutes', 360),
            (int) config('services.github.repository_sync.next_max_minutes', 720)
        ));
    }

    private function retrySyncDueAt(Carbon $from): Carbon
    {
        return $from->copy()->addMinutes(random_int(
            (int) config('services.github.repository_sync.retry_min_minutes', 120),
            (int) config('services.github.repository_sync.retry_max_minutes', 240)
        ));
    }

    private function repositoryPayload(array $repository): array
    {
        return Arr::only($repository, [
            'id',
            'name',
            'full_name',
            'html_url',
            'default_branch',
            'open_issues_count',
            'stargazers_count',
            'forks_count',
            'pushed_at',
        ]);
    }

    private function repositoryProviderId(array $repository): ?string
    {
        $id = Arr::get($repository, 'id');

        if (! is_scalar($id)) {
            return null;
        }

        return (string) $id;
    }

    private function repositoryOwner(array $repository, ?string $fallback): ?string
    {
        $owner = Arr::get($repository, 'owner.login');

        if (is_string($owner) && $owner !== '') {
            return $owner;
        }

        $fullName = Arr::get($repository, 'full_name');

        if (is_string($fullName) && str_contains($fullName, '/')) {
            return Str::before($fullName, '/');
        }

        return $fallback;
    }

    private function commitMessage(?array $latestCommit): ?string
    {
        $message = Arr::get($latestCommit ?? [], 'commit.message');

        if (! is_string($message) || $message === '') {
            return null;
        }

        return Str::limit(Str::before($message, "\n"), 255, '');
    }

    private function commitAuthor(?array $latestCommit): ?string
    {
        $login = Arr::get($latestCommit ?? [], 'author.login');

        if (is_string($login) && $login !== '') {
            return $login;
        }

        $name = Arr::get($latestCommit ?? [], 'commit.author.name');

        if (! is_string($name) || $name === '') {
            return null;
        }

        return $name;
    }

    private function parseDate(mixed $value): ?Carbon
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        return Carbon::parse($value);
    }
}
