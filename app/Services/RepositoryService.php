<?php

namespace App\Services;

use App\Models\Idea;
use App\Repositories\Ideas\IdeaRepository;
use App\Services\ThirdParty\GitHub\GitHubService;
use Exception;
use Illuminate\Support\Facades\Auth;

/**
 * Class RepositoryService
 */
class RepositoryService
{
    public function __construct(private IdeaRepository $ideas) {}

    public function create(Idea $idea): bool
    {
        GitHubService::createClient(Auth::user()->github_token)
            ->repo()->create($idea->repository_name);

        return $this->ideas->markRepositoryCreated($idea);
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
            GitHubService::createClient(Auth::user()->github_token)
                ->repo()->collaborators()->add(
                    Auth::user()->github_username,
                    $idea->repository_name,
                    $collaborator->user->github_username
                );
        } catch (Exception $e) {
            return false;
        }

        return true;
    }
}
