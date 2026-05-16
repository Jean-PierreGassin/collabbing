<?php

namespace Tests\Feature;

use App\Models\Idea;
use App\Models\User;
use App\Services\RepositoryService;
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
        $user = User::factory()->create([
            'github_token' => null,
            'github_username' => null,
        ]);
        $idea = Idea::factory()->for($user)->create([
            'repository' => false,
            'repository_name' => 'collab-idea',
        ]);

        $repositoryService = Mockery::mock(RepositoryService::class);
        $repositoryService->shouldReceive('create')
            ->once()
            ->with(Mockery::on(fn (Idea $candidate) => $candidate->is($idea)))
            ->andThrow(new Exception('Repository creation failed'));

        $this->instance(RepositoryService::class, $repositoryService);

        $response = $this
            ->actingAs($user)
            ->get(route('ideas.repository-create', $idea));

        $response
            ->assertRedirect(route('ideas.dashboard', $idea))
            ->assertSessionHasErrors(['repository' => 'Repository creation failed']);

        $this
            ->get(route('ideas.dashboard', $idea))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Ideas/Manage'));
    }
}
