<?php

namespace App\Services;

use App\Models\CodeRepository;
use App\Models\Idea;
use App\Models\IdeaApplication;
use App\Services\Ideas\IdeaRepositorySyncService;
use App\Services\ThirdParty\GitHub\GitHubRepositoryClient;
use RuntimeException;
use Throwable;

/**
 * Class RepositoryService
 */
class RepositoryService
{
    public function __construct(
        private GitHubRepositoryClient $github,
        private IdeaRepositorySyncService $sync
    ) {}

    public function create(Idea $idea): bool
    {
        $idea->loadMissing('user.githubAccount', 'codeRepository');

        if (! $idea->codeRepository instanceof CodeRepository) {
            throw new RuntimeException('Repository settings are missing.');
        }

        $repository = $this->github->create($idea->user, $idea->codeRepository->name);

        $this->sync->recordCreated($idea->codeRepository, $repository);

        return true;
    }

    public function inviteUsers(Idea $idea): bool
    {
        return $idea->approvedApplications()
            ->with('user.githubAccount')
            ->get()
            ->every(fn (IdeaApplication $collaborator) => $this->inviteUser($idea, $collaborator));
    }

    public function inviteUser(Idea $idea, IdeaApplication $collaborator): bool
    {
        $idea->loadMissing('user.githubAccount', 'codeRepository');
        $collaborator->loadMissing('user.githubAccount');

        $collaboratorUsername = $collaborator->user->githubUsername();

        if (! $idea->codeRepository instanceof CodeRepository || ! $collaboratorUsername) {
            return false;
        }

        try {
            $this->github->addCollaborator($idea->user, $idea->codeRepository->name, $collaboratorUsername);
        } catch (Throwable) {
            return false;
        }

        return true;
    }
}
