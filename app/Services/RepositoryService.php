<?php

namespace App\Services;

use App\Models\Idea;
use App\Models\IdeaApplication;
use App\Repositories\Ideas\IdeaRepository;
use App\Services\Ideas\IdeaRepositorySyncService;
use App\Services\ThirdParty\GitHub\GitHubRepositoryClient;
use Throwable;

/**
 * Class RepositoryService
 */
class RepositoryService
{
    public function __construct(
        private IdeaRepository $ideas,
        private GitHubRepositoryClient $github,
        private IdeaRepositorySyncService $sync
    ) {}

    public function create(Idea $idea): bool
    {
        $repository = $this->github->create($idea->user, $idea->repository_name);

        $created = $this->ideas->markRepositoryCreated($idea);

        $this->sync->recordCreated($idea->refresh(), $repository);

        return $created;
    }

    public function inviteUsers(Idea $idea): bool
    {
        return $this->ideas
            ->getApprovedApplications($idea)
            ->every(fn (IdeaApplication $collaborator) => $this->inviteUser($idea, $collaborator));
    }

    public function inviteUser(Idea $idea, IdeaApplication $collaborator): bool
    {
        if (! $collaborator->user->github_username) {
            return false;
        }

        try {
            $this->github->addCollaborator($idea->user, $idea->repository_name, $collaborator->user->github_username);
        } catch (Throwable) {
            return false;
        }

        return true;
    }
}
