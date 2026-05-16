<?php

namespace App\Services\Inertia;

use App\Models\Idea;
use App\Models\IdeaApplication;
use App\Models\IdeaComment;
use App\Models\IdeaSupporter;
use App\Models\User;
use GrahamCampbell\Markdown\Facades\Markdown;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;

class PagePropsService
{
    public function user(?User $user): ?array
    {
        if (! $user) {
            return null;
        }

        return [
            'id' => $user->id,
            'username' => $user->username,
            'firstName' => $user->first_name,
            'lastName' => $user->last_name,
            'name' => $user->name,
            'email' => $user->email,
            'bio' => $user->bio,
            'bioHtml' => $user->bio ? (string) Markdown::convertToHtml($user->bio) : null,
            'githubUsername' => $user->github_username,
            'hasGithubToken' => (bool) $user->github_token,
            'profilePicture' => $user->profilePicture(),
            'createdAtFormatted' => date('d M - Y', $user->created_at->timestamp),
            'canUpdate' => Gate::allows('update', $user),
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
            'supporters',
            'approvedApplications.user',
        ]);

        return [
            'id' => $idea->id,
            'title' => $idea->title,
            'titleDisplay' => ucfirst($idea->title),
            'communication' => $idea->communication,
            'content' => $idea->content,
            'contentHtml' => (string) Markdown::convertToHtml($idea->content),
            'status' => $idea->status,
            'statusDisplay' => ucfirst($idea->status),
            'repository' => $idea->repository,
            'repositoryName' => $idea->repository_name,
            'createdAtForHumans' => $idea->created_at->diffForHumans(),
            'user' => $this->user($idea->user),
            'supportersCount' => $idea->supporters->count(),
            'approvedApplicationsCount' => $idea->approvedApplications->count(),
            'collaborators' => $idea->approvedApplications->map(fn (IdeaApplication $application) => $this->application($application))->values(),
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
                'repositoryCreate' => $idea->user->github_token
                    ? route('ideas.repository-create', $idea)
                    : route('auth.github.login'),
                'repositoryInvite' => $idea->user->github_token
                    ? route('ideas.repository-invite', $idea)
                    : route('auth.github.login'),
            ],
        ];
    }

    public function comment(IdeaComment $comment): array
    {
        $comment->loadMissing('user');

        return [
            'id' => $comment->id,
            'content' => $comment->content,
            'createdAtForHumans' => $comment->created_at->diffForHumans(),
            'updatedAtForHumans' => $comment->updated_at->diffForHumans(),
            'wasEdited' => $comment->created_at->timestamp < $comment->updated_at->timestamp,
            'user' => $this->user($comment->user),
            'can' => [
                'update' => Gate::allows('update', $comment),
            ],
            'routes' => [
                'edit' => route('ideas.comments.edit', [$comment->idea_id, $comment]),
                'update' => route('ideas.comments.update', [$comment->idea_id, $comment]),
            ],
        ];
    }

    public function application(IdeaApplication $application): array
    {
        $application->loadMissing('user');

        return [
            'id' => $application->id,
            'content' => $application->content,
            'status' => $application->status,
            'createdAtForHumans' => $application->created_at->diffForHumans(),
            'user' => $this->user($application->user),
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
