<?php

namespace App\Services;

use App\Models\Idea;
use App\Models\User;
use App\Services\ThirdParty\GitHub\GitHubService;
use Exception;
use Illuminate\Support\Facades\Auth;

/**
 * Class RepositoryService
 * @package App\Services
 */
class RepositoryService
{
    private GitHubService $gitHubService;

    public function __construct(GitHubService $gitHubService)
    {
        $this->gitHubService = $gitHubService;
    }

    /**
     * @param Idea $idea
     * @return bool
     */
    public function create(Idea $idea): bool
    {
        $this->gitHubService::createClient(Auth::user()->github_token)
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
        $user = User::find(Auth::user()->id);

        try {
            $gitHub = Auth::user()->createGithubClient();

            $gitHub->repo()->collaborators()->add(
                $user->github_username,
                $idea->repository_name,
                $collaborator->user->github_username
            );
        } catch (Exception $e) {
            return false;
        }

        return true;
    }
}