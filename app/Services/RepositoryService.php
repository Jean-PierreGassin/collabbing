<?php

namespace App\Services;

use App\Models\Idea;
use App\Services\ThirdParty\GitHub\GitHubService;
use Exception;
use Illuminate\Support\Facades\Auth;

/**
 * Class RepositoryService
 * @package App\Services
 */
class RepositoryService
{
    /**
     * @param Idea $idea
     * @return bool
     */
    public function create(Idea $idea): bool
    {
        GitHubService::createClient(Auth::user()->github_token)
            ->repo()->create($idea->repository_name);

        $idea->update(
            [
                'repository' => true,
            ]
        );

        return true;
    }

    /**
     * @param Idea $idea
     * @return bool
     */
    public function inviteUsers(Idea $idea): bool
    {
        foreach ($idea->approvedApplications()->get() as $collaborator) {
            $this->inviteUser($idea, $collaborator);
        }

        return true;
    }

    /**
     * @param Idea $idea
     * @param $collaborator
     * @return bool
     */
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