<?php

namespace Tests\Unit\Services;

use App\Models\CodeRepository;
use App\Models\ConnectedAccount;
use App\Models\Idea;
use App\Models\IdeaApplication;
use App\Models\User;
use App\Services\Ideas\IdeaRepositoryReadmeService;
use App\Services\Ideas\IdeaRepositorySyncService;
use App\Services\RepositoryService;
use App\Services\ThirdParty\GitHub\GitHubRepositoryClient;
use Exception;
use PHPUnit\Framework\TestCase;

class RepositoryServiceTest extends TestCase
{
    public function testCreateBootstrapsRepositoryReadmeFromIdea(): void
    {
        $owner = $this->githubUser('owner');
        $idea = $this->idea($owner);

        $github = $this->createMock(GitHubRepositoryClient::class);
        $github->expects($this->once())
            ->method('create')
            ->with(
                $this->callback(fn (User $candidate) => $candidate->is($owner)),
                'collab-idea',
            )
            ->willReturn($this->repositoryPayload());
        $github->expects($this->once())
            ->method('createReadme')
            ->with(
                $this->callback(fn (User $candidate) => $candidate->is($owner)),
                'collab-idea',
                $this->callback(fn (string $content): bool => str_contains($content, '# Useful collaboration idea')
                    && str_contains($content, 'A short summary for collaborators.')
                    && str_contains($content, 'Preferred communication: Slack.')
                    && str_contains($content, '## Pitch')
                    && str_contains($content, 'A focused pitch for the team.')),
            );

        $sync = $this->createMock(IdeaRepositorySyncService::class);
        $sync->expects($this->once())
            ->method('recordCreated')
            ->with(
                $this->callback(fn (CodeRepository $candidate) => $candidate->name === 'collab-idea'),
                $this->repositoryPayload(),
            );

        $this->assertTrue($this->service($github, $sync)->create($idea));
    }

    public function testCreateDoesNotWriteReadmeWhenRepositoryCreationFails(): void
    {
        $owner = $this->githubUser('owner');
        $idea = $this->idea($owner);

        $github = $this->createMock(GitHubRepositoryClient::class);
        $github->expects($this->once())
            ->method('create')
            ->willThrowException(new Exception('GitHub create failed'));
        $github->expects($this->never())->method('createReadme');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('GitHub create failed');

        $this->service($github)->create($idea);
    }

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

    private function service(GitHubRepositoryClient $github, ?IdeaRepositorySyncService $sync = null): RepositoryService
    {
        return new RepositoryService(
            github: $github,
            sync: $sync ?? new IdeaRepositorySyncService($this->createStub(GitHubRepositoryClient::class)),
            readmes: new IdeaRepositoryReadmeService,
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
        $idea->forceFill([
            'title' => 'Useful collaboration idea',
            'summary' => 'A short summary for collaborators.',
            'communication' => 'Slack',
            'content' => 'A focused pitch for the team.',
        ]);
        $idea->setRelation('codeRepository', new CodeRepository([
            'provider' => CodeRepository::PROVIDER_GITHUB,
            'status' => CodeRepository::STATUS_ACTIVE,
            'name' => 'collab-idea',
        ]));

        return $idea;
    }

    private function repositoryPayload(): array
    {
        return [
            'id' => 123,
            'name' => 'collab-idea',
            'full_name' => 'owner/collab-idea',
            'html_url' => 'https://github.com/owner/collab-idea',
            'default_branch' => 'main',
            'open_issues_count' => 0,
            'stargazers_count' => 0,
            'forks_count' => 0,
        ];
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
