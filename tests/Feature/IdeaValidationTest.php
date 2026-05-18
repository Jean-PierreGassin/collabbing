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

    public function testIdeaCanBeCreated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post(route('ideas.store'), $this->ideaPayload());

        $idea = Idea::query()->where('title', 'A useful collaboration tool')->first();

        $this->assertNotNull($idea);

        $response->assertRedirect(route('ideas.show', $idea));
        $this->assertSame($user->id, $idea->user_id);
        $this->assertDatabaseHas('code_repositories', [
            'idea_id' => $idea->id,
            'provider' => CodeRepository::PROVIDER_GITHUB,
            'status' => CodeRepository::STATUS_PLANNED,
            'name' => 'useful-collaboration-tool',
        ]);
    }

    public function testIdeaCanBeEdited(): void
    {
        $user = User::factory()->create();
        $idea = Idea::factory()
            ->for($user, 'user')
            ->withCodeRepository('original-repository')
            ->create();

        $response = $this
            ->actingAs($user)
            ->put(route('ideas.update', $idea), $this->ideaPayload([
                'title' => 'An updated collaboration tool',
                'summary' => 'A better summary for the updated collaboration tool.',
                'repository_name' => 'updated-collaboration-tool',
                'status' => 'closed',
            ]));

        $response->assertRedirect(route('ideas.show', $idea));
        $this->assertDatabaseHas('ideas', [
            'id' => $idea->id,
            'user_id' => $user->id,
            'title' => 'An updated collaboration tool',
            'summary' => 'A better summary for the updated collaboration tool.',
            'status' => 'closed',
        ]);
        $this->assertDatabaseHas('code_repositories', [
            'idea_id' => $idea->id,
            'provider' => CodeRepository::PROVIDER_GITHUB,
            'name' => 'updated-collaboration-tool',
        ]);
    }

    public function testRepositoryNameRejectsSpaces(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post(route('ideas.store'), $this->ideaPayload([
                'repository_name' => 'repo with spaces',
            ]));

        $response->assertSessionHasErrors('repository_name');
    }

    public function testIdeaSummaryIsRequiredAndLimited(): void
    {
        $user = User::factory()->create();

        $missingSummary = $this
            ->actingAs($user)
            ->post(route('ideas.store'), $this->ideaPayload(['summary' => null]));

        $missingSummary->assertSessionHasErrors('summary');

        $longSummary = $this
            ->actingAs($user)
            ->post(route('ideas.store'), $this->ideaPayload([
                'summary' => str_repeat('a', 241),
            ]));

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
            ->post(route('ideas.store'), $this->ideaPayload([
                'repository_name' => 'existing-repo',
            ]))
            ->assertSessionHasErrors('repository_name');
    }

    private function ideaPayload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'A useful collaboration tool',
            'summary' => 'A short summary for a useful collaboration tool.',
            'repository_name' => 'useful-collaboration-tool',
            'communication' => 'Slack',
            'content' => 'A focused pitch for a useful collaboration tool.',
            'status' => 'open',
        ], $overrides);
    }
}
