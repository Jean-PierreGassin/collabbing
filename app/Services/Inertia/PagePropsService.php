<?php

namespace App\Services\Inertia;

use App\Models\Idea;
use App\Models\IdeaApplication;
use App\Models\IdeaComment;
use App\Models\IdeaSupporter;
use App\Models\RepositoryEvent;
use App\Models\User;
use DateTimeInterface;
use GrahamCampbell\Markdown\Facades\Markdown;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class PagePropsService
{
    private const IDEA_SUMMARY_LIMIT = 240;

    private const COLLABORATOR_PREVIEW_LIMIT = 12;

    public function user(?User $user): ?array
    {
        if (! $user) {
            return null;
        }

        $canUpdate = Gate::allows('update', $user);

        return [
            'id' => $user->id,
            'username' => $user->username,
            'firstName' => $user->first_name,
            'lastName' => $user->last_name,
            'name' => $user->name,
            'email' => $canUpdate ? $user->email : null,
            'bio' => $user->bio,
            'bioHtml' => $user->bio ? (string) Markdown::convertToHtml($user->bio) : null,
            'githubUsername' => $user->githubUsername(),
            'hasGithubToken' => $user->hasGithubToken(),
            'profilePicture' => $user->profilePicture(),
            'createdAtFormatted' => date('d M - Y', $user->created_at->timestamp),
            'canUpdate' => $canUpdate,
            'routes' => [
                'show' => route('users.show', $user->username),
                'edit' => route('users.edit', $user->username),
                'update' => route('users.update', $user->username),
                'githubLogin' => route('auth.github.login'),
                'githubRevoke' => route('auth.github.revoke'),
            ],
        ];
    }

    public function idea(Idea $idea): array
    {
        $idea->loadMissing([
            'user',
            'codeRepository.events',
        ]);

        $codeRepository = $idea->latestCodeRepository();
        $owner = $idea->owner();
        $supportersCount = $this->relationCount($idea, 'supporters', 'supporters_count');
        $approvedApplicationsCount = $this->relationCount($idea, 'approvedApplications', 'approved_applications_count');
        $collaborators = $this->collaboratorPreview($idea);

        return [
            'id' => $idea->id,
            'title' => $idea->title,
            'titleDisplay' => ucfirst($idea->title),
            'summary' => $this->ideaSummary($idea),
            'communication' => $idea->communication,
            'content' => $idea->content,
            'contentHtml' => (string) Markdown::convertToHtml($idea->content),
            'status' => $idea->status,
            'statusDisplay' => ucfirst($idea->status),
            'repository' => $codeRepository?->isAvailable() ?? false,
            'repositoryName' => $codeRepository?->name,
            'repositoryActivity' => $this->repositoryActivity($idea),
            'createdAtForHumans' => $idea->created_at->diffForHumans(),
            'user' => $this->user($owner),
            'supportersCount' => $supportersCount,
            'approvedApplicationsCount' => $approvedApplicationsCount,
            'collaborators' => $collaborators->map(fn (IdeaApplication $application) => $this->application($application))->values(),
            'hiddenCollaboratorsCount' => max(0, $approvedApplicationsCount - $collaborators->count()),
            'can' => [
                'update' => Gate::allows('update', $idea),
                'storeApplication' => Gate::allows('storeApplication', $idea),
                'storeSupporter' => Gate::allows('storeSupporter', $idea),
                'deleteApplication' => Gate::allows('deleteApplication', $idea),
                'updateApplication' => Gate::allows('updateApplication', $idea),
            ],
            'routes' => [
                'show' => route('ideas.show', $idea),
                'edit' => route('ideas.edit', $idea),
                'dashboard' => route('ideas.dashboard', $idea),
                'update' => route('ideas.update', $idea),
                'applicationsCreate' => route('ideas.applications.create', $idea),
                'applicationsStore' => route('ideas.applications.store', $idea),
                'commentsStore' => route('ideas.comments.store', $idea),
                'supportersStore' => route('ideas.supporters.store', $idea),
                'repositoryCreate' => $owner?->hasGithubToken()
                    ? route('ideas.repository-create', $idea)
                    : route('auth.github.login'),
                'repositoryInvite' => $owner?->hasGithubToken()
                    ? route('ideas.repository-invite', $idea)
                    : route('auth.github.login'),
            ],
        ];
    }

    private function ideaSummary(Idea $idea): string
    {
        if ($idea->summary) {
            return $idea->summary;
        }

        $text = trim((string) preg_replace(
            '/\s+/',
            ' ',
            strip_tags((string) Markdown::convertToHtml($idea->content))
        ));

        return Str::limit($text ?: $idea->title, self::IDEA_SUMMARY_LIMIT, '');
    }

    private function relationCount(Idea $idea, string $relation, string $countAttribute): int
    {
        $count = $idea->getAttribute($countAttribute);

        if (is_numeric($count)) {
            return (int) $count;
        }

        if ($idea->relationLoaded($relation)) {
            return $idea->{$relation}->count();
        }

        return $idea->{$relation}()->count();
    }

    private function collaboratorPreview(Idea $idea): Collection
    {
        return IdeaApplication::query()
            ->where('idea_id', $idea->id)
            ->where('status', 'approved')
            ->with('user')
            ->latest()
            ->limit(self::COLLABORATOR_PREVIEW_LIMIT)
            ->get();
    }

    private function repositoryActivity(Idea $idea): array
    {
        $codeRepository = $idea->latestCodeRepository();

        $events = [];

        if ($codeRepository && ($codeRepository->isAvailable() || $codeRepository->missing_at)) {
            $events = RepositoryEvent::query()
                ->where('code_repository_id', $codeRepository->id)
                ->latest('occurred_at')
                ->limit(5)
                ->get()
                ->map(fn (RepositoryEvent $event): array => [
                    'id' => $event->id,
                    'type' => $event->type,
                    'summary' => $event->summary,
                    'occurredAtForHumans' => $this->dateForHumans($event->occurred_at),
                ])
                ->values();
        }

        return [
            'htmlUrl' => $codeRepository?->html_url,
            'defaultBranch' => $codeRepository?->default_branch,
            'isMissing' => (bool) $codeRepository?->missing_at,
            'openIssuesCount' => (int) $codeRepository?->open_issues_count,
            'stargazersCount' => (int) $codeRepository?->stargazers_count,
            'forksCount' => (int) $codeRepository?->forks_count,
            'lastPushedAtForHumans' => $this->dateForHumans($codeRepository?->pushed_at),
            'lastSyncedAtForHumans' => $this->dateForHumans($codeRepository?->synced_at),
            'latestCommitSha' => $codeRepository?->latest_commit_sha,
            'latestCommitShortSha' => $codeRepository?->latest_commit_sha
                ? substr($codeRepository->latest_commit_sha, 0, 7)
                : null,
            'latestCommitMessage' => $codeRepository?->latest_commit_message,
            'latestCommitAuthor' => $codeRepository?->latest_commit_author,
            'events' => $events,
        ];
    }

    private function dateForHumans(mixed $value): ?string
    {
        if (! $value instanceof DateTimeInterface) {
            return null;
        }

        if (! method_exists($value, 'diffForHumans')) {
            return null;
        }

        return $value->diffForHumans();
    }

    public function comment(IdeaComment $comment): array
    {
        $comment->loadMissing(['user', 'replies.user']);

        $contentHtml = $this->commentContentHtml($comment);

        return [
            'id' => $comment->id,
            'parentId' => $comment->parent_id,
            'content' => $comment->content,
            'contentHtml' => $contentHtml,
            'createdAtForHumans' => $comment->created_at->diffForHumans(),
            'updatedAtForHumans' => $comment->updated_at->diffForHumans(),
            'wasEdited' => $comment->created_at->timestamp < $comment->updated_at->timestamp,
            'user' => $this->user($comment->user instanceof User ? $comment->user : null),
            'replies' => IdeaComment::query()
                ->where('parent_id', $comment->id)
                ->with('user')
                ->oldest()
                ->get()
                ->map(fn (IdeaComment $reply): array => $this->comment($reply))
                ->values(),
            'can' => [
                'update' => Gate::allows('update', $comment),
            ],
            'routes' => [
                'edit' => route('ideas.comments.edit', [$comment->idea_id, $comment]),
                'update' => route('ideas.comments.update', [$comment->idea_id, $comment]),
            ],
        ];
    }

    private function commentContentHtml(IdeaComment $comment): string
    {
        $usernames = collect();

        preg_match_all('/(?<![A-Za-z0-9_-])@([A-Za-z0-9_-]{3,20})\b/', $comment->content, $matches);

        if (! empty($matches[1])) {
            $usernames = User::query()
                ->whereIn('username', array_unique($matches[1]))
                ->get()
                ->keyBy('username');
        }

        $content = preg_replace_callback(
            '/(?<![A-Za-z0-9_-])@([A-Za-z0-9_-]{3,20})\b/',
            function (array $matches) use ($usernames): string {
                $user = $usernames->get($matches[1]);

                if (! $user instanceof User) {
                    return $matches[0];
                }

                return sprintf('[@%s](%s)', $user->username, route('users.show', $user->username));
            },
            $comment->content
        );

        return (string) Markdown::convertToHtml($content ?? $comment->content);
    }

    public function application(IdeaApplication $application): array
    {
        $application->loadMissing('user');

        return [
            'id' => $application->id,
            'content' => $application->content,
            'contentHtml' => (string) Markdown::convertToHtml($application->content),
            'status' => $application->status,
            'createdAtForHumans' => $application->created_at->diffForHumans(),
            'user' => $this->user($application->user instanceof User ? $application->user : null),
            'routes' => [
                'destroy' => route('ideas.applications.destroy', [$application->idea_id, $application]),
                'approve' => route('ideas.applications.approve', [$application->idea_id, $application]),
            ],
        ];
    }

    public function supporter(?IdeaSupporter $supporter): ?array
    {
        if (! $supporter) {
            return null;
        }

        return [
            'id' => $supporter->id,
            'routes' => [
                'destroy' => route('ideas.supporters.destroy', [$supporter->idea_id, $supporter]),
            ],
        ];
    }

    public function paginator(LengthAwarePaginator $paginator, callable $mapItem): array
    {
        return [
            'items' => $paginator->getCollection()->map($mapItem)->values(),
            'currentPage' => $paginator->currentPage(),
            'lastPage' => $paginator->lastPage(),
            'previousPageUrl' => $paginator->previousPageUrl(),
            'nextPageUrl' => $paginator->nextPageUrl(),
        ];
    }
}
