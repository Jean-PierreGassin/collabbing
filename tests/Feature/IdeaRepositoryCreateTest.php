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
use Mockery;
use Tests\TestCase;

class IdeaRepositoryCreateTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_repository_create_failure_returns_to_dashboard_with_renderable_errors(): void
    {
        $user = User::factory()->create();
        $idea = Idea::factory()->for($user)->withCodeRepository('collab-idea')->create();

        $repositoryService = Mockery::mock(RepositoryService::class);
        $repositoryService->shouldReceive('create')
            ->once()
            ->with(Mockery::on(fn (Idea $candidate) => $candidate->is($idea)))
            ->andThrow(new Exception('Repository creation failed'));

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

    public function test_repository_creation_cannot_be_triggered_by_get_requests(): void
    {
        $user = User::factory()->create();
        $idea = Idea::factory()->for($user)->withCodeRepository('collab-idea')->create();

        $response = $this
            ->actingAs($user)
            ->get(route('ideas.repository-create', $idea));

        $response->assertStatus(405);
    }

    public function test_repository_creation_redirects_when_repository_already_exists(): void
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

    public function test_repository_invites_cannot_be_triggered_by_get_requests(): void
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

    public function test_repository_invite_failure_returns_actionable_status(): void
    {
        $user = User::factory()->create();
        $idea = Idea::factory()
            ->for($user)
            ->withCodeRepository('collab-idea', ['status' => CodeRepository::STATUS_ACTIVE])
            ->create();

        $repositoryService = Mockery::mock(RepositoryService::class);
        $repositoryService->shouldReceive('inviteUsers')
            ->once()
            ->with(Mockery::on(fn (Idea $candidate) => $candidate->is($idea)))
            ->andReturnFalse();

        $this->instance(RepositoryService::class, $repositoryService);

        $this
            ->actingAs($user)
            ->post(route('ideas.repository-invite', $idea))
            ->assertRedirect(route('ideas.dashboard', $idea))
            ->assertSessionHas('status', 'Repository invitations could not be sent. Please try again.');
    }

    public function test_repository_invites_report_failure_when_any_collaborator_cannot_be_invited(): void
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

        $github = Mockery::mock(GitHubRepositoryClient::class);
        $github->shouldReceive('addCollaborator')
            ->once()
            ->with(
                Mockery::on(fn (User $candidate) => $candidate->is($owner)),
                'collab-idea',
                'collaborator'
            )
            ->andThrow(new Exception('GitHub invite failed'));

        $service = new RepositoryService($github, Mockery::mock(IdeaRepositorySyncService::class));

        $this->assertFalse($service->inviteUsers($idea));
    }

    public function test_repository_invites_fail_when_collaborator_has_no_github_username(): void
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

        $github = Mockery::mock(GitHubRepositoryClient::class);
        $github->shouldNotReceive('addCollaborator');

        $service = new RepositoryService($github, Mockery::mock(IdeaRepositorySyncService::class));

        $this->assertFalse($service->inviteUsers($idea));
    }
}
