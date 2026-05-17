<?php

namespace Tests\Feature;

use App\Models\CodeRepository;
use App\Models\ConnectedAccount;
use App\Models\Idea;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class IdeaValidationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_repository_name_rejects_spaces(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post(route('ideas.store'), [
                'title' => 'A useful collaboration tool',
                'summary' => 'A short summary for a useful collaboration tool.',
                'repository_name' => 'repo with spaces',
                'communication' => 'Slack',
                'content' => 'A focused pitch for a useful collaboration tool.',
                'status' => 'open',
            ]);

        $response->assertSessionHasErrors('repository_name');
    }

    public function test_idea_summary_is_required_and_limited(): void
    {
        $user = User::factory()->create();

        $missingSummary = $this
            ->actingAs($user)
            ->post(route('ideas.store'), [
                'title' => 'A useful collaboration tool',
                'repository_name' => 'useful-collaboration-tool',
                'communication' => 'Slack',
                'content' => 'A focused pitch for a useful collaboration tool.',
                'status' => 'open',
            ]);

        $missingSummary->assertSessionHasErrors('summary');

        $longSummary = $this
            ->actingAs($user)
            ->post(route('ideas.store'), [
                'title' => 'A useful collaboration tool',
                'summary' => str_repeat('a', 241),
                'repository_name' => 'useful-collaboration-tool',
                'communication' => 'Slack',
                'content' => 'A focused pitch for a useful collaboration tool.',
                'status' => 'open',
            ]);

        $longSummary->assertSessionHasErrors('summary');
    }

    public function test_repository_name_must_be_unique_for_connected_github_owner(): void
    {
        $user = User::factory()->create();

        ConnectedAccount::factory()
            ->for($user, 'user')
            ->create([
                'provider_username' => 'octocat',
            ]);

        $existingIdea = Idea::factory()
            ->for($user, 'user')
            ->create();

        CodeRepository::factory()
            ->for($existingIdea, 'idea')
            ->create([
                'provider' => CodeRepository::PROVIDER_GITHUB,
                'owner' => 'octocat',
                'name' => 'existing-repo',
            ]);

        $this
            ->actingAs($user)
            ->post(route('ideas.store'), [
                'title' => 'A useful collaboration tool',
                'summary' => 'A short summary for a useful collaboration tool.',
                'repository_name' => 'existing-repo',
                'communication' => 'Slack',
                'content' => 'A focused pitch for a useful collaboration tool.',
                'status' => 'open',
            ])
            ->assertSessionHasErrors('repository_name');
    }
}
