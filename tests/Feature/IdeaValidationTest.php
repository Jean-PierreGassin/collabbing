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

    public function testRepositoryNameRejectsSpaces(): void
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

    public function testIdeaSummaryIsRequiredAndLimited(): void
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

    public function testRepositoryNameMustBeUniqueForConnectedGithubOwner(): void
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
