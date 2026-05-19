<?php

namespace App\Services;

use App\Models\Idea;
use App\Models\IdeaApplication;
use App\Models\User;
use App\Services\Ideas\IdeaRepositoryReadmeService;
use App\Services\Ideas\IdeaRepositorySyncService;
use App\Services\ThirdParty\GitHub\GitHubRepositoryClient;
use RuntimeException;
use Throwable;

class RepositoryService
{
    public function __construct(
        private GitHubRepositoryClient $github,
        private IdeaRepositorySyncService $sync,
        private IdeaRepositoryReadmeService $readmes
    ) {}

    public function create(Idea $idea): bool
    {
        $idea->loadMissing('user.githubAccount', 'codeRepository');

        $owner = $idea->owner();
        $codeRepository = $idea->latestCodeRepository();

        if (! $owner || ! $codeRepository) {
            throw new RuntimeException('Repository settings are missing.');
        }

        $repository = $this->github->create($owner, $codeRepository->name);
        $this->github->createReadme($owner, $codeRepository->name, $this->readmes->build($idea, $codeRepository));

        $this->sync->recordCreated($codeRepository, $repository);

        return true;
    }

    public function inviteUsers(Idea $idea): bool
    {
        return IdeaApplication::query()
            ->where('idea_id', $idea->id)
            ->where('status', 'approved')
            ->with('user.githubAccount')
            ->get()
            ->every(fn (IdeaApplication $collaborator) => $this->inviteUser($idea, $collaborator));
    }

    public function inviteUser(Idea $idea, IdeaApplication $collaborator): bool
    {
        $idea->loadMissing('user.githubAccount', 'codeRepository');
        $collaborator->loadMissing('user.githubAccount');

        $owner = $idea->owner();
        $codeRepository = $idea->latestCodeRepository();
        $collaboratorUser = $collaborator->user;
        $collaboratorUsername = null;

        if ($collaboratorUser instanceof User) {
            $collaboratorUsername = $collaboratorUser->githubUsername();
        }

        if (! $owner || ! $codeRepository || ! $collaboratorUsername) {
            return false;
        }

        try {
            $this->github->addCollaborator($owner, $codeRepository->name, $collaboratorUsername);
        } catch (Throwable) {
            return false;
        }

        return true;
    }
}
