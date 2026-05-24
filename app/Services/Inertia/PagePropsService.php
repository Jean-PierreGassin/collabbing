<?php

namespace App\Services\Inertia;

use App\Models\Idea;
use App\Models\IdeaApplication;
use App\Models\IdeaApplicationMessage;
use App\Models\IdeaComment;
use App\Models\IdeaSupporter;
use App\Models\RepositoryEvent;
use App\Models\User;
use App\Repositories\CodeRepositories\RepositoryEventRepository;
use App\Repositories\Ideas\ApplicationRepository;
use App\Repositories\Ideas\CommentRepository;
use Carbon\Carbon;
use GrahamCampbell\Markdown\Facades\Markdown;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class PagePropsService
{
    private const IDEA_SUMMARY_LIMIT = 240;

    private const IDEA_TAGLINE_LIMIT = 60;

    private const COLLABORATOR_PREVIEW_LIMIT = 12;

    public function __construct(
        private ApplicationRepository $applications,
        private CommentRepository $comments,
        private RepositoryEventRepository $repositoryEvents
    ) {}

    public function user(?User $user): ?array
    {
        if (! $user) {
            return null;
        }

        $canUpdate = Gate::allows('update', $user);
        $email = null;
        $bioHtml = null;

        if ($canUpdate) {
            $email = $user->email;
        }

        if ($user->bio) {
            $bioHtml = (string) Markdown::convertToHtml($user->bio);
        }

        return [
            'id' => $user->id,
            'username' => $user->username,
            'firstName' => $user->first_name,
            'lastName' => $user->last_name,
            'name' => $user->name,
            'email' => $email,
            'bio' => $user->bio,
            'bioHtml' => $bioHtml,
            'githubUsername' => $user->githubUsername(),
            'hasGithubToken' => $user->hasGithubToken(),
            'profilePicture' => $user->profilePicture(),
            'createdAtFormatted' => $user->created_at->format('d M - Y'),
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
        $canUpdate = Gate::allows('update', $idea);
        $supportersCount = $this->relationCount($idea, 'supporters', 'supporters_count');
        $approvedApplicationsCount = $this->relationCount($idea, 'approvedApplications', 'approved_applications_count');
        $pendingApplicationsCount = null;
        $collaborators = $this->collaboratorPreview($idea);
        $repositoryAvailable = false;
        $repositoryCreateRoute = route('auth.github.login');
        $repositoryInviteRoute = route('auth.github.login');

        if ($codeRepository) {
            $repositoryAvailable = $codeRepository->isAvailable();
        }

        if ($owner && $owner->hasGithubToken()) {
            $repositoryCreateRoute = route('ideas.repository-create', $idea);
            $repositoryInviteRoute = route('ideas.repository-invite', $idea);
        }

        if ($canUpdate) {
            $pendingApplicationsCount = $this->relationCount($idea, 'pendingApplications', 'pending_applications_count');
        }

        $privateGettingStartedNotes = $this->privateGettingStartedNotes($idea);

        return [
            'id' => $idea->id,
            'title' => $idea->title,
            'titleDisplay' => ucfirst($idea->title),
            'tagline' => $this->ideaTagline($idea),
            'summary' => $this->ideaSummary($idea),
            'tags' => $this->ideaTags($idea),
            'communication' => $idea->communication,
            'collaboration' => [
                'stage' => $idea->collaboration_stage,
                'stageDisplay' => $this->collaborationStageDisplay($idea->collaboration_stage),
                'helpWanted' => $this->helpWanted($idea),
                'helpWantedDisplay' => $this->helpWantedDisplay($idea),
                'helpWantedNote' => $idea->help_wanted_note,
                'firstContribution' => $idea->first_contribution,
                'applicationsOpen' => $idea->applications_open,
                'applicationsClosedNote' => $idea->applications_closed_note,
                'communicationStyle' => $idea->communication_style,
                'communicationStyleDisplay' => $this->communicationStyleDisplay($idea->communication_style),
                'communicationNote' => $idea->communication_note,
                'gettingStartedNotesReady' => $this->hasGettingStartedNotes($idea),
                'gettingStartedNotes' => $privateGettingStartedNotes,
                'gettingStartedNotesHtml' => $this->privateGettingStartedNotesHtml($privateGettingStartedNotes),
                'gettingStartedNotesUpdatedAtForHumans' => $this->dateForHumans($idea->getting_started_notes_updated_at),
                'readinessBadges' => [
                    'applicationsOpen' => $idea->applications_open,
                    'firstStepListed' => $this->hasFirstContribution($idea),
                    'repoAvailable' => $repositoryAvailable,
                    'startNotesReady' => $this->hasGettingStartedNotes($idea),
                ],
            ],
            'content' => $idea->content,
            'contentHtml' => (string) Markdown::convertToHtml($idea->content),
            'status' => $idea->status,
            'statusDisplay' => ucfirst($idea->status),
            'repository' => $repositoryAvailable,
            'repositoryName' => $codeRepository?->name,
            'repositoryActivity' => $this->repositoryActivity($idea),
            'createdAtForHumans' => $idea->created_at->diffForHumans(),
            'user' => $this->user($owner),
            'supportersCount' => $supportersCount,
            'approvedApplicationsCount' => $approvedApplicationsCount,
            'pendingApplicationsCount' => $pendingApplicationsCount,
            'collaborators' => $collaborators->map(fn (IdeaApplication $application) => $this->application($application))->values(),
            'hiddenCollaboratorsCount' => max(0, $approvedApplicationsCount - $collaborators->count()),
            'can' => [
                'update' => $canUpdate,
                'storeApplication' => Gate::allows('storeApplication', $idea),
                'storeSupporter' => Gate::allows('storeSupporter', $idea),
                'deleteApplication' => Gate::allows('deleteApplication', $idea),
                'updateApplication' => Gate::allows('updateApplication', $idea),
                'storeComment' => Gate::allows('storeComment', $idea),
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
                'repositoryCreate' => $repositoryCreateRoute,
                'repositoryInvite' => $repositoryInviteRoute,
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

        if ($text === '') {
            $text = $idea->title;
        }

        return Str::limit($text, self::IDEA_SUMMARY_LIMIT, '');
    }

    private function ideaTagline(Idea $idea): string
    {
        if ($idea->tagline) {
            return Str::limit($idea->tagline, self::IDEA_TAGLINE_LIMIT, '');
        }

        return Str::limit("Open for collaborators around {$idea->title}.", self::IDEA_TAGLINE_LIMIT, '');
    }

    private function ideaTags(Idea $idea): array
    {
        $tags = $idea->getAttributeValue('tags');

        if (! is_array($tags)) {
            return [];
        }

        return collect($tags)
            ->filter(fn (mixed $tag): bool => is_string($tag) && trim($tag) !== '')
            ->map(fn (string $tag): string => Str::of($tag)->squish()->lower()->toString())
            ->unique()
            ->values()
            ->all();
    }

    private function helpWanted(Idea $idea): array
    {
        $helpWanted = $idea->getAttributeValue('help_wanted');

        if (! is_array($helpWanted)) {
            return [];
        }

        return collect($helpWanted)
            ->filter(fn (mixed $area): bool => is_string($area) && trim($area) !== '')
            ->map(fn (string $area): string => Str::of($area)->squish()->lower()->toString())
            ->unique()
            ->values()
            ->all();
    }

    private function helpWantedDisplay(Idea $idea): array
    {
        return collect($this->helpWanted($idea))
            ->map(fn (string $area): string => $this->helpAreaDisplay($area))
            ->values()
            ->all();
    }

    private function collaborationStageDisplay(?string $stage): string
    {
        return match ($stage) {
            Idea::COLLABORATION_STAGE_ROUGH_IDEA => 'Rough idea',
            Idea::COLLABORATION_STAGE_NEEDS_SHAPING => 'Needs shaping',
            Idea::COLLABORATION_STAGE_READY_TO_BUILD => 'Ready to build',
            Idea::COLLABORATION_STAGE_ACTIVELY_BUILDING => 'Actively building',
            Idea::COLLABORATION_STAGE_LIVE => 'Live',
            default => 'Not decided yet',
        };
    }

    private function helpAreaDisplay(string $area): string
    {
        return match ($area) {
            Idea::HELP_FRONTEND => 'Frontend',
            Idea::HELP_BACKEND => 'Backend',
            Idea::HELP_DESIGN => 'Design',
            Idea::HELP_PRODUCT => 'Product',
            Idea::HELP_TESTING => 'Testing',
            Idea::HELP_DEVOPS => 'DevOps',
            Idea::HELP_WRITING => 'Writing',
            Idea::HELP_RESEARCH => 'Research',
            Idea::HELP_FEEDBACK => 'Feedback',
            Idea::HELP_MARKETING => 'Marketing',
            Idea::HELP_ANYTHING => 'Open to anything',
            default => Str::of($area)->replace('_', ' ')->title()->toString(),
        };
    }

    private function communicationStyleDisplay(?string $style): string
    {
        return match ($style) {
            Idea::COMMUNICATION_STYLE_GITHUB => 'GitHub',
            Idea::COMMUNICATION_STYLE_DISCORD => 'Discord',
            Idea::COMMUNICATION_STYLE_SLACK => 'Slack',
            Idea::COMMUNICATION_STYLE_EMAIL => 'Email',
            Idea::COMMUNICATION_STYLE_CALLS => 'Calls',
            Idea::COMMUNICATION_STYLE_NOT_DECIDED => 'Not decided yet',
            default => 'Not decided yet',
        };
    }

    private function hasFirstContribution(Idea $idea): bool
    {
        return is_string($idea->first_contribution) && trim($idea->first_contribution) !== '';
    }

    private function hasGettingStartedNotes(Idea $idea): bool
    {
        return is_string($idea->getting_started_notes) && trim($idea->getting_started_notes) !== '';
    }

    private function privateGettingStartedNotes(Idea $idea): ?string
    {
        if (! $this->hasGettingStartedNotes($idea)) {
            return null;
        }

        if (! $this->canViewPrivateGettingStartedNotes($idea)) {
            return null;
        }

        return $idea->getting_started_notes;
    }

    private function privateGettingStartedNotesHtml(?string $notes): ?string
    {
        if ($notes === null) {
            return null;
        }

        return (string) Markdown::convertToHtml($notes);
    }

    private function canViewPrivateGettingStartedNotes(Idea $idea): bool
    {
        if (Gate::allows('update', $idea)) {
            return true;
        }

        $user = Auth::user();

        if (! $user instanceof User) {
            return false;
        }

        return $idea->applications()
            ->where('user_id', $user->id)
            ->where('status', IdeaApplication::STATUS_APPROVED)
            ->exists();
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
        return $this->applications->getApprovedApplicationPreview($idea, self::COLLABORATOR_PREVIEW_LIMIT);
    }

    private function repositoryActivity(Idea $idea): array
    {
        $codeRepository = $idea->latestCodeRepository();

        $events = [];

        if ($codeRepository && ($codeRepository->isAvailable() || $codeRepository->missing_at)) {
            $events = $this->repositoryEvents
                ->recentFor($codeRepository, 5)
                ->map(fn (RepositoryEvent $event): array => [
                    'id' => $event->id,
                    'type' => $event->type,
                    'summary' => $event->summary,
                    'occurredAtForHumans' => $this->dateForHumans($event->occurred_at),
                ])
                ->values();
        }

        $latestCommitShortSha = null;

        if ($codeRepository?->latest_commit_sha) {
            $latestCommitShortSha = substr($codeRepository->latest_commit_sha, 0, 7);
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
            'latestCommitShortSha' => $latestCommitShortSha,
            'latestCommitMessage' => $codeRepository?->latest_commit_message,
            'latestCommitAuthor' => $codeRepository?->latest_commit_author,
            'events' => $events,
        ];
    }

    private function dateForHumans(mixed $value): ?string
    {
        if (! $value instanceof Carbon) {
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
            'user' => $this->user($this->commentUser($comment)),
            'replies' => $this->comments
                ->repliesFor($comment)
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

        $usernames = $this->comments->mentionedUsers($matches[1]);

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
            'contributionType' => $application->contribution_type,
            'contributionTypeDisplay' => $this->applicationContributionDisplay($application->contribution_type),
            'firstAction' => $application->first_action,
            'approvalNote' => $this->privateApplicationText($application, 'approval_note'),
            'approvalNoteHtml' => $this->privateApplicationTextHtml($application, 'approval_note'),
            'declineReason' => $this->privateApplicationText($application, 'decline_reason'),
            'status' => $application->status,
            'statusDisplay' => $this->applicationStatusDisplay($application->status),
            'createdAtForHumans' => $application->created_at->diffForHumans(),
            'user' => $this->user($this->applicationUser($application)),
            'thread' => $this->applicationThread($application),
            'routes' => [
                'destroy' => route('ideas.applications.destroy', [$application->idea_id, $application]),
                'edit' => route('ideas.applications.edit', [$application->idea_id, $application]),
                'update' => route('ideas.applications.update', [$application->idea_id, $application]),
                'approve' => route('ideas.applications.approve', [$application->idea_id, $application]),
            ],
        ];
    }

    private function applicationThread(IdeaApplication $application): ?array
    {
        $user = Auth::user();

        if (! $user instanceof User || Gate::denies('viewThread', $application)) {
            return null;
        }

        $unreadCount = $this->applications->unreadMessagesCount($application, $user);
        $canMessage = Gate::allows('message', $application);
        $messages = $this->applications
            ->messagesFor($application)
            ->map(fn (IdeaApplicationMessage $message): array => $this->applicationMessage($message))
            ->values();

        return [
            'messages' => $messages,
            'unreadCount' => $unreadCount,
            'hasUnread' => $unreadCount > 0,
            'canMessage' => $canMessage,
            'isReadOnly' => ! $canMessage,
            'readOnlyReason' => $this->applicationThreadReadOnlyReason($application, $canMessage),
            'routes' => [
                'read' => route('ideas.applications.read-state.update', [$application->idea_id, $application]),
                'store' => route('ideas.applications.messages.store', [$application->idea_id, $application]),
            ],
        ];
    }

    private function privateApplicationText(IdeaApplication $application, string $key): ?string
    {
        if (Gate::denies('viewThread', $application)) {
            return null;
        }

        $value = $application->getAttributeValue($key);

        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        return $value;
    }

    private function privateApplicationTextHtml(IdeaApplication $application, string $key): ?string
    {
        $value = $this->privateApplicationText($application, $key);

        if ($value === null) {
            return null;
        }

        return (string) Markdown::convertToHtml($value);
    }

    private function applicationMessage(IdeaApplicationMessage $message): array
    {
        $message->loadMissing('user');
        $body = $message->body;
        $bodyHtml = null;

        if ($body) {
            $bodyHtml = (string) Markdown::convertToHtml($body);
        }

        return [
            'id' => $message->id,
            'type' => $message->type,
            'body' => $body,
            'bodyHtml' => $bodyHtml,
            'isSystem' => $message->type !== IdeaApplicationMessage::TYPE_MESSAGE,
            'occurredAtForHumans' => $this->dateForHumans($message->occurred_at),
            'user' => $this->user($this->messageUser($message)),
        ];
    }

    private function applicationThreadReadOnlyReason(IdeaApplication $application, bool $canMessage): ?string
    {
        if ($canMessage) {
            return null;
        }

        if ($application->isPending()) {
            return null;
        }

        return 'This application thread is read-only after a final decision.';
    }

    private function applicationContributionDisplay(?string $contributionType): string
    {
        if (! $contributionType) {
            return 'Not specified';
        }

        return $this->helpAreaDisplay($contributionType);
    }

    private function applicationStatusDisplay(string $status): string
    {
        return match ($status) {
            IdeaApplication::STATUS_PENDING => 'Pending',
            IdeaApplication::STATUS_WITHDRAWN => 'Withdrawn',
            IdeaApplication::STATUS_APPROVED => 'Collaborating',
            IdeaApplication::STATUS_DECLINED => 'Declined',
            IdeaApplication::STATUS_LEFT => 'Left collaboration',
            IdeaApplication::STATUS_REMOVED => 'Removed from collaboration',
            default => ucfirst($status),
        };
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

    private function commentUser(IdeaComment $comment): ?User
    {
        if (! $comment->user instanceof User) {
            return null;
        }

        return $comment->user;
    }

    private function applicationUser(IdeaApplication $application): ?User
    {
        if (! $application->user instanceof User) {
            return null;
        }

        return $application->user;
    }

    private function messageUser(IdeaApplicationMessage $message): ?User
    {
        if (! $message->user instanceof User) {
            return null;
        }

        return $message->user;
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
