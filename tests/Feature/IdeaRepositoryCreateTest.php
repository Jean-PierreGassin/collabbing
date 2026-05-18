<?php

namespace Tests\Feature;

use App\Models\CodeRepository;
use App\Models\Idea;
use App\Models\IdeaApplication;
use App\Models\User;
use App\Services\Ideas\IdeaRepositorySyncService;
use App\Services\RepositoryService;
use App\Services\ThirdParty\GitHub\GitHubRepositoryClient;
use Exception;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class IdeaRepositoryCreateTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function testRepositoryCreateFailureReturnsToDashboardWithRenderableErrors(): void
    {
        $user = User::factory()->create();
        $idea = Idea::factory()->for($user)->withCodeRepository('collab-idea')->create();

        $repositoryService = $this->createMock(RepositoryService::class);
        $repositoryService->expects($this->once())
            ->method('create')
            ->with($this->callback(fn (Idea $candidate) => $candidate->is($idea)))
            ->willThrowException(new Exception('Repository creation failed'));

        $this->instance(RepositoryService::class, $repositoryService);

        $response = $this
            ->actingAs($user)
            ->post(route('ideas.repository-create', $idea));

        $response
            ->assertRedirect(route('ideas.dashboard', $idea))
            ->assertSessionHasErrors(['repository' => 'Repository creation failed']);

        $this
            ->get(route('ideas.dashboard', $idea))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Ideas/Manage'));
    }

    public function testRepositoryCreationCannotBeTriggeredByGetRequests(): void
    {
        $user = User::factory()->create();
        $idea = Idea::factory()->for($user)->withCodeRepository('collab-idea')->create();

        $response = $this
            ->actingAs($user)
            ->get(route('ideas.repository-create', $idea));

        $response->assertStatus(405);
    }

    public function testRepositoryCreationRedirectsWhenRepositoryAlreadyExists(): void
    {
        $user = User::factory()->create();
        $idea = Idea::factory()
            ->for($user)
            ->withCodeRepository('collab-idea', ['status' => CodeRepository::STATUS_ACTIVE])
            ->create();

        $this
            ->actingAs($user)
            ->post(route('ideas.repository-create', $idea))
            ->assertRedirect(route('ideas.dashboard', $idea));
    }

    public function testRepositoryInvitesCannotBeTriggeredByGetRequests(): void
    {
        $user = User::factory()->create();
        $idea = Idea::factory()
            ->for($user)
            ->withCodeRepository('collab-idea', ['status' => CodeRepository::STATUS_ACTIVE])
            ->create();

        $response = $this
            ->actingAs($user)
            ->get(route('ideas.repository-invite', $idea));

        $response->assertStatus(405);
    }

    public function testRepositoryInviteFailureReturnsActionableStatus(): void
    {
        $user = User::factory()->create();
        $idea = Idea::factory()
            ->for($user)
            ->withCodeRepository('collab-idea', ['status' => CodeRepository::STATUS_ACTIVE])
            ->create();

        $repositoryService = $this->createMock(RepositoryService::class);
        $repositoryService->expects($this->once())
            ->method('inviteUsers')
            ->with($this->callback(fn (Idea $candidate) => $candidate->is($idea)))
            ->willReturn(false);

        $this->instance(RepositoryService::class, $repositoryService);

        $this
            ->actingAs($user)
            ->post(route('ideas.repository-invite', $idea))
            ->assertRedirect(route('ideas.dashboard', $idea))
            ->assertSessionHas('status', 'Repository invitations could not be sent. Please try again.');
    }

    public function testRepositoryInvitesReportFailureWhenAnyCollaboratorCannotBeInvited(): void
    {
        $owner = User::factory()->withGithubAccount('owner-token', 'owner')->create();
        $collaborator = User::factory()->withGithubAccount(null, 'collaborator')->create();
        $idea = Idea::factory()
            ->for($owner)
            ->withCodeRepository('collab-idea', ['status' => CodeRepository::STATUS_ACTIVE])
            ->create();
        IdeaApplication::factory()
            ->for($idea, 'idea')
            ->for($collaborator, 'user')
            ->create([
                'status' => 'approved',
            ]);

        $github = $this->createMock(GitHubRepositoryClient::class);
        $github->expects($this->once())
            ->method('addCollaborator')
            ->with(
                $this->callback(fn (User $candidate) => $candidate->is($owner)),
                'collab-idea',
                'collaborator'
            )
            ->willThrowException(new Exception('GitHub invite failed'));

        $service = new RepositoryService($github, $this->syncService());

        $this->assertFalse($service->inviteUsers($idea));
    }

    public function testRepositoryInvitesFailWhenCollaboratorHasNoGithubUsername(): void
    {
        $owner = User::factory()->withGithubAccount('owner-token', 'owner')->create();
        $collaborator = User::factory()->create();
        $idea = Idea::factory()
            ->for($owner)
            ->withCodeRepository('collab-idea', ['status' => CodeRepository::STATUS_ACTIVE])
            ->create();
        IdeaApplication::factory()
            ->for($idea, 'idea')
            ->for($collaborator, 'user')
            ->create([
                'status' => 'approved',
            ]);

        $github = $this->createMock(GitHubRepositoryClient::class);
        $github->expects($this->never())->method('addCollaborator');

        $service = new RepositoryService($github, $this->syncService());

        $this->assertFalse($service->inviteUsers($idea));
    }

    private function syncService(): IdeaRepositorySyncService
    {
        return new IdeaRepositorySyncService($this->createStub(GitHubRepositoryClient::class));
    }
}
