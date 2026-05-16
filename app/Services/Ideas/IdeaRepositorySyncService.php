<?php

namespace App\Services\Ideas;

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

    public function sync(Idea $idea): void
    {
        $idea->loadMissing('user');

        if (! $idea->repository || ! $idea->user instanceof User || ! $idea->user->github_token || ! $idea->user->github_username || ! $idea->repository_name) {
            return;
        }

        try {
            $repository = $this->github->show($idea->user, $idea->repository_name);
        } catch (GitHubRuntimeException $exception) {
            if ($exception->getCode() === 404) {
                $this->markMissing($idea);

                return;
            }

            throw $exception;
        }

        $branch = Arr::get($repository, 'default_branch');
        $latestCommit = $this->github->latestCommit($idea->user, $idea->repository_name, is_string($branch) ? $branch : null);

        $this->recordSnapshot($idea, $repository, $latestCommit);
    }

    public function recordCreated(Idea $idea, array $repository): void
    {
        $this->recordSnapshot($idea, $repository, null);

        $this->recordEvent(
            $idea,
            'repository_created',
            'Repository created on GitHub.',
            'repository_created:'.$idea->repository_name,
            now(),
            $this->repositoryPayload($repository)
        );
    }

    public function recordSnapshot(Idea $idea, array $repository, ?array $latestCommit): void
    {
        $wasSynced = (bool) $idea->repository_synced_at;
        $previousBranch = $idea->repository_default_branch;
        $previousIssuesCount = $idea->repository_open_issues_count;
        $previousCommitSha = $idea->repository_latest_commit_sha;
        $previousMissingAt = $idea->repository_missing_at;

        $latestCommitSha = Arr::get($latestCommit ?? [], 'sha');
        $latestCommitMessage = $this->commitMessage($latestCommit);
        $latestCommitAuthor = $this->commitAuthor($latestCommit);
        $pushedAt = $this->parseDate(Arr::get($repository, 'pushed_at'));
        $syncedAt = now();

        $idea->forceFill([
            'repository' => true,
            'repository_html_url' => Arr::get($repository, 'html_url'),
            'repository_default_branch' => Arr::get($repository, 'default_branch'),
            'repository_open_issues_count' => (int) Arr::get($repository, 'open_issues_count', 0),
            'repository_stargazers_count' => (int) Arr::get($repository, 'stargazers_count', 0),
            'repository_forks_count' => (int) Arr::get($repository, 'forks_count', 0),
            'repository_latest_commit_sha' => $latestCommitSha,
            'repository_latest_commit_message' => $latestCommitMessage,
            'repository_latest_commit_author' => $latestCommitAuthor,
            'repository_pushed_at' => $pushedAt,
            'repository_synced_at' => $syncedAt,
            'repository_missing_at' => null,
            'repository_sync_due_at' => $this->nextSyncDueAt($syncedAt),
        ])->save();

        if (! $wasSynced) {
            $this->recordEvent(
                $idea,
                'repository_synced',
                'Repository sync connected to GitHub.',
                'repository_synced:'.$idea->repository_name,
                $syncedAt,
                $this->repositoryPayload($repository)
            );
        }

        if ($previousMissingAt) {
            $this->recordEvent(
                $idea,
                'repository_restored',
                'Repository is available on GitHub again.',
                'repository_restored:'.$idea->repository_name.':'.$syncedAt->timestamp,
                $syncedAt,
                $this->repositoryPayload($repository)
            );
        }

        if ($latestCommitSha && $latestCommitSha !== $previousCommitSha) {
            $this->recordEvent(
                $idea,
                'repository_commit',
                $latestCommitMessage ? 'Latest commit: '.$latestCommitMessage : 'Repository received a new commit.',
                'commit:'.$latestCommitSha,
                $this->parseDate(Arr::get($latestCommit ?? [], 'commit.committer.date')) ?? $pushedAt ?? $syncedAt,
                [
                    'sha' => $latestCommitSha,
                    'message' => $latestCommitMessage,
                    'author' => $latestCommitAuthor,
                ]
            );
        }

        if ($wasSynced && $previousBranch && $previousBranch !== $idea->repository_default_branch) {
            $this->recordEvent(
                $idea,
                'repository_branch_changed',
                "Default branch changed from {$previousBranch} to {$idea->repository_default_branch}.",
                'default_branch:'.$idea->repository_default_branch.':'.$syncedAt->timestamp,
                $syncedAt,
                ['previous' => $previousBranch, 'current' => $idea->repository_default_branch]
            );
        }

        if ($wasSynced && $previousIssuesCount !== $idea->repository_open_issues_count) {
            $this->recordEvent(
                $idea,
                'repository_issues_changed',
                "Open issues changed from {$previousIssuesCount} to {$idea->repository_open_issues_count}.",
                'open_issues:'.$idea->repository_open_issues_count.':'.$syncedAt->timestamp,
                $syncedAt,
                ['previous' => $previousIssuesCount, 'current' => $idea->repository_open_issues_count]
            );
        }
    }

    private function markMissing(Idea $idea): void
    {
        $missingAt = now();

        $idea->forceFill([
            'repository' => false,
            'repository_synced_at' => $missingAt,
            'repository_missing_at' => $missingAt,
            'repository_sync_due_at' => null,
        ])->save();

        $this->recordEvent(
            $idea,
            'repository_missing',
            'Repository is no longer available on GitHub.',
            'repository_missing:'.$idea->repository_name.':'.$missingAt->toDateString(),
            $missingAt,
            ['repository_name' => $idea->repository_name]
        );
    }

    private function recordEvent(Idea $idea, string $type, string $summary, string $dedupeKey, Carbon $occurredAt, array $payload = []): void
    {
        $idea->repositoryEvents()->firstOrCreate(
            ['dedupe_key' => $dedupeKey],
            [
                'type' => $type,
                'summary' => Str::limit($summary, 255, ''),
                'occurred_at' => $occurredAt,
                'payload' => $payload,
            ]
        );
    }

    public function scheduleRetry(Idea $idea): void
    {
        $idea->forceFill([
            'repository_sync_due_at' => $this->retrySyncDueAt(now()),
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

        return is_string($name) && $name !== '' ? $name : null;
    }

    private function parseDate(mixed $value): ?Carbon
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        return Carbon::parse($value);
    }
}
