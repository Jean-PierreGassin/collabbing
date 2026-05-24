<?php

namespace Tests\Feature\Ideas;

use App\Models\CodeRepository;
use App\Models\ConnectedAccount;
use App\Models\Idea;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
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

    public function testIdeaCanBeCreatedWithCollaborationSetup(): void
    {
        $user = User::factory()->create();

        $this
            ->actingAs($user)
            ->post(route('ideas.store'), $this->ideaPayload([
                'collaboration_stage' => Idea::COLLABORATION_STAGE_READY_TO_BUILD,
                'help_wanted' => [
                    Idea::HELP_FRONTEND,
                    Idea::HELP_BACKEND,
                ],
                'help_wanted_note' => 'Pairing across frontend and backend would help.',
                'first_contribution' => 'Open a small pull request for the onboarding copy.',
                'applications_open' => false,
                'applications_closed_note' => 'Reviewing existing interest before reopening.',
                'communication_style' => Idea::COMMUNICATION_STYLE_GITHUB,
                'communication_note' => 'Issues and pull requests first.',
                'getting_started_notes' => 'Private accepted-collaborator context.',
            ]))
            ->assertRedirect();

        $idea = Idea::query()->where('title', 'A useful collaboration tool')->firstOrFail();

        $this->assertSame(Idea::COLLABORATION_STAGE_READY_TO_BUILD, $idea->collaboration_stage);
        $this->assertSame([Idea::HELP_FRONTEND, Idea::HELP_BACKEND], $idea->help_wanted);
        $this->assertFalse($idea->applications_open);
        $this->assertSame(Idea::COMMUNICATION_STYLE_GITHUB, $idea->communication_style);
        $this->assertSame('Private accepted-collaborator context.', $idea->getting_started_notes);
        $this->assertNotNull($idea->getting_started_notes_updated_at);
    }

    public function testIdeaCanBeCreatedWithUndecidedCollaborationDetails(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post(route('ideas.store'), $this->ideaPayload([
                'collaboration_stage' => null,
                'help_wanted' => [],
                'help_wanted_note' => null,
                'first_contribution' => null,
                'applications_open' => true,
                'applications_closed_note' => null,
                'communication_style' => null,
                'communication_note' => null,
                'getting_started_notes' => null,
            ]));

        $idea = Idea::query()->where('title', 'A useful collaboration tool')->firstOrFail();

        $response->assertRedirect(route('ideas.show', $idea));
        $this->assertNull($idea->collaboration_stage);
        $this->assertSame([], $idea->help_wanted);
        $this->assertTrue($idea->applications_open);
        $this->assertNull($idea->getting_started_notes_updated_at);
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
                'tagline' => 'A sharper card tagline for the update.',
                'summary' => 'A better summary for the updated collaboration tool.',
                'tags' => 'design, launch',
                'repository_name' => 'updated-collaboration-tool',
                'status' => 'closed',
            ]));

        $response->assertRedirect(route('ideas.show', $idea));
        $this->assertDatabaseHas('ideas', [
            'id' => $idea->id,
            'user_id' => $user->id,
            'title' => 'An updated collaboration tool',
            'tagline' => 'A sharper card tagline for the update.',
            'summary' => 'A better summary for the updated collaboration tool.',
            'status' => 'closed',
        ]);
        $this->assertSame(['design', 'launch'], $idea->fresh()->tags);
        $this->assertDatabaseHas('code_repositories', [
            'idea_id' => $idea->id,
            'provider' => CodeRepository::PROVIDER_GITHUB,
            'name' => 'updated-collaboration-tool',
        ]);
    }

    public function testIdeaCollaborationSetupCanBeEdited(): void
    {
        $user = User::factory()->create();
        $idea = Idea::factory()
            ->for($user, 'user')
            ->withCodeRepository('original-repository')
            ->create([
                'collaboration_stage' => Idea::COLLABORATION_STAGE_ROUGH_IDEA,
                'help_wanted' => [Idea::HELP_PRODUCT],
                'getting_started_notes' => 'Original notes.',
                'getting_started_notes_updated_at' => Carbon::parse('2026-05-20 00:00:00', 'UTC'),
            ]);

        $this
            ->actingAs($user)
            ->put(route('ideas.update', $idea), $this->ideaPayload([
                'repository_name' => 'updated-collaboration-tool',
                'collaboration_stage' => Idea::COLLABORATION_STAGE_ACTIVELY_BUILDING,
                'help_wanted' => [
                    Idea::HELP_DESIGN,
                    Idea::HELP_TESTING,
                ],
                'help_wanted_note' => 'Design critique and regression tests.',
                'first_contribution' => 'Start by reviewing the empty state.',
                'applications_open' => false,
                'applications_closed_note' => 'Temporarily paused.',
                'communication_style' => Idea::COMMUNICATION_STYLE_DISCORD,
                'communication_note' => 'Async check-ins.',
                'getting_started_notes' => 'Updated private notes.',
            ]))
            ->assertRedirect(route('ideas.show', $idea));

        $updatedIdea = $idea->fresh();

        $this->assertSame(Idea::COLLABORATION_STAGE_ACTIVELY_BUILDING, $updatedIdea->collaboration_stage);
        $this->assertSame([Idea::HELP_DESIGN, Idea::HELP_TESTING], $updatedIdea->help_wanted);
        $this->assertFalse($updatedIdea->applications_open);
        $this->assertSame('Updated private notes.', $updatedIdea->getting_started_notes);

        $updatedAt = $updatedIdea->getAttributeValue('getting_started_notes_updated_at');

        $this->assertInstanceOf(Carbon::class, $updatedAt);
        $this->assertTrue($updatedAt->greaterThan(Carbon::parse('2026-05-20 00:00:00', 'UTC')));
        $this->assertDatabaseHas('code_repositories', [
            'idea_id' => $idea->id,
            'name' => 'updated-collaboration-tool',
        ]);
    }

    public function testEditingPreservesOmittedCollaborationFields(): void
    {
        $user = User::factory()->create();
        $timestamp = Carbon::parse('2026-05-20 00:00:00', 'UTC');
        $idea = Idea::factory()
            ->for($user, 'user')
            ->withCodeRepository('original-repository')
            ->create([
                'collaboration_stage' => Idea::COLLABORATION_STAGE_NEEDS_SHAPING,
                'help_wanted' => [Idea::HELP_WRITING],
                'help_wanted_note' => 'Copy help.',
                'first_contribution' => 'Review the pitch.',
                'applications_open' => false,
                'applications_closed_note' => 'Closed for review.',
                'communication_style' => Idea::COMMUNICATION_STYLE_SLACK,
                'communication_note' => 'Async first.',
                'getting_started_notes' => 'Original private notes.',
                'getting_started_notes_updated_at' => $timestamp,
            ]);

        $this
            ->actingAs($user)
            ->put(route('ideas.update', $idea), [
                'title' => 'An updated collaboration tool',
                'tagline' => 'A sharper card tagline for the update.',
                'summary' => 'A better summary for the updated collaboration tool.',
                'tags' => 'design, launch',
                'repository_name' => 'updated-collaboration-tool',
                'communication' => 'Slack',
                'content' => 'A focused pitch for a useful collaboration tool.',
                'status' => 'open',
            ])
            ->assertRedirect(route('ideas.show', $idea));

        $updatedIdea = $idea->fresh();

        $this->assertSame(Idea::COLLABORATION_STAGE_NEEDS_SHAPING, $updatedIdea->collaboration_stage);
        $this->assertSame([Idea::HELP_WRITING], $updatedIdea->help_wanted);
        $this->assertFalse($updatedIdea->applications_open);
        $this->assertSame('Original private notes.', $updatedIdea->getting_started_notes);

        $updatedAt = $updatedIdea->getAttributeValue('getting_started_notes_updated_at');

        $this->assertInstanceOf(Carbon::class, $updatedAt);
        $this->assertTrue($updatedAt->equalTo($timestamp));
    }

    #[DataProvider('invalidIdeaPayloads')]
    public function testIdeaPayloadRejectsInvalidInput(array $overrides, string $errorKey): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post(route('ideas.store'), $this->ideaPayload($overrides));

        $response->assertSessionHasErrors($errorKey);
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
            'tagline' => 'Match collaborators around useful product work.',
            'summary' => 'A short summary for a useful collaboration tool.',
            'tags' => 'product, collaboration',
            'repository_name' => 'useful-collaboration-tool',
            'communication' => 'Slack',
            'content' => 'A focused pitch for a useful collaboration tool.',
            'status' => 'open',
        ], $overrides);
    }

    public static function invalidIdeaPayloads(): array
    {
        return [
            'repository name with spaces' => [
                ['repository_name' => 'repo with spaces'],
                'repository_name',
            ],
            'missing summary' => [
                ['summary' => null],
                'summary',
            ],
            'missing tagline' => [
                ['tagline' => null],
                'tagline',
            ],
            'long tagline' => [
                ['tagline' => str_repeat('a', 61)],
                'tagline',
            ],
            'long summary' => [
                ['summary' => str_repeat('a', 241)],
                'summary',
            ],
            'too many tags' => [
                ['tags' => 'one,two,three,four,five,six,seven,eight,nine'],
                'tags',
            ],
            'invalid collaboration stage' => [
                ['collaboration_stage' => 'planning'],
                'collaboration_stage',
            ],
            'invalid help wanted area' => [
                ['help_wanted' => ['frontend', 'finance']],
                'help_wanted.1',
            ],
            'too many help wanted areas' => [
                ['help_wanted' => array_fill(0, 12, Idea::HELP_FRONTEND)],
                'help_wanted',
            ],
            'invalid communication style' => [
                ['communication_style' => 'sms'],
                'communication_style',
            ],
            'long help note' => [
                ['help_wanted_note' => str_repeat('a', 241)],
                'help_wanted_note',
            ],
            'long first contribution' => [
                ['first_contribution' => str_repeat('a', 1201)],
                'first_contribution',
            ],
            'long applications closed note' => [
                ['applications_closed_note' => str_repeat('a', 241)],
                'applications_closed_note',
            ],
            'long communication note' => [
                ['communication_note' => str_repeat('a', 241)],
                'communication_note',
            ],
            'long getting started notes' => [
                ['getting_started_notes' => str_repeat('a', 10001)],
                'getting_started_notes',
            ],
        ];
    }
}
