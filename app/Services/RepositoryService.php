<?php

namespace App\Services;

use App\Models\Idea;
use App\Repositories\Ideas\IdeaRepository;
use App\Services\Ideas\IdeaRepositorySyncService;
use App\Services\ThirdParty\GitHub\GitHubRepositoryClient;
use Exception;
use Illuminate\Support\Facades\Auth;

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
        $repository = $this->github->create(Auth::user(), $idea->repository_name);

        $created = $this->ideas->markRepositoryCreated($idea);

        $this->sync->recordCreated($idea->refresh(), $repository);

        return $created;
    }

    public function inviteUsers(Idea $idea): bool
    {
        foreach ($this->ideas->getApprovedApplications($idea) as $collaborator) {
            $this->inviteUser($idea, $collaborator);
        }

        return true;
    }

    public function inviteUser(Idea $idea, $collaborator): bool
    {
        try {
            $this->github->addCollaborator(Auth::user(), $idea->repository_name, $collaborator->user->github_username);
        } catch (Exception $e) {
            return false;
        }

        return true;
    }
}
