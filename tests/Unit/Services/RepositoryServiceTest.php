<?php

namespace Tests\Unit\Services;

use App\Models\CodeRepository;
use App\Models\ConnectedAccount;
use App\Models\Idea;
use App\Models\IdeaApplication;
use App\Models\User;
use App\Services\Ideas\IdeaRepositorySyncService;
use App\Services\RepositoryService;
use App\Services\ThirdParty\GitHub\GitHubRepositoryClient;
use Exception;
use PHPUnit\Framework\TestCase;

class RepositoryServiceTest extends TestCase
{
    public function testInviteUserInvitesGithubCollaborator(): void
    {
        $owner = $this->githubUser('owner');
        $collaborator = $this->githubUser('approved-user');
        $idea = $this->idea($owner);
        $application = $this->application($collaborator);

        $github = $this->createMock(GitHubRepositoryClient::class);
        $github->expects($this->once())
            ->method('addCollaborator')
            ->with(
                $this->callback(fn (User $candidate) => $candidate->is($owner)),
                'collab-idea',
                'approved-user',
            );

        $this->assertTrue($this->service($github)->inviteUser($idea, $application));
    }

    public function testInviteUserReturnsFalseWhenGithubInviteFails(): void
    {
        $owner = $this->githubUser('owner');
        $collaborator = $this->githubUser('collaborator');
        $idea = $this->idea($owner);
        $application = $this->application($collaborator);

        $github = $this->createMock(GitHubRepositoryClient::class);
        $github->expects($this->once())
            ->method('addCollaborator')
            ->willThrowException(new Exception('GitHub invite failed'));

        $this->assertFalse($this->service($github)->inviteUser($idea, $application));
    }

    public function testInviteUserSkipsCollaboratorsWithoutGithubUsername(): void
    {
        $owner = $this->githubUser('owner');
        $collaborator = new User;
        $collaborator->setRelation('githubAccount', null);
        $idea = $this->idea($owner);
        $application = $this->application($collaborator);

        $github = $this->createMock(GitHubRepositoryClient::class);
        $github->expects($this->never())->method('addCollaborator');

        $this->assertFalse($this->service($github)->inviteUser($idea, $application));
    }

    private function service(GitHubRepositoryClient $github): RepositoryService
    {
        return new RepositoryService(
            github: $github,
            sync: new IdeaRepositorySyncService($this->createStub(GitHubRepositoryClient::class)),
        );
    }

    private function githubUser(string $username): User
    {
        $user = new User;
        $user->setRelation('githubAccount', new ConnectedAccount([
            'provider' => User::PROVIDER_GITHUB,
            'provider_username' => $username,
        ]));

        return $user;
    }

    private function idea(User $owner): Idea
    {
        $idea = new class extends Idea
        {
            public function loadMissing($relations)
            {
                return $this;
            }
        };

        $idea->setRelation('user', $owner);
        $idea->setRelation('codeRepository', new CodeRepository([
            'provider' => CodeRepository::PROVIDER_GITHUB,
            'status' => CodeRepository::STATUS_ACTIVE,
            'name' => 'collab-idea',
        ]));

        return $idea;
    }

    private function application(User $collaborator): IdeaApplication
    {
        $application = new class extends IdeaApplication
        {
            public function loadMissing($relations)
            {
                return $this;
            }
        };

        $application->setRelation('user', $collaborator);

        return $application;
    }
}
