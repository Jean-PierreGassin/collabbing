<?php

namespace Tests\Feature\Ideas;

use App\Models\Idea;
use App\Models\IdeaApplication;
use App\Models\IdeaApplicationMessage;
use App\Models\IdeaApplicationReadState;
use App\Models\User;
use App\Services\Inertia\PagePropsService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CollaborationDataFoundationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function testIdeaStoresCollaborationSetup(): void
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
                'help_wanted_note' => 'A small UI plus API pairing would help.',
                'first_contribution' => "Pick one tiny bug and open a PR.\nThe issue list is public.",
                'applications_open' => false,
                'applications_closed_note' => 'Pausing applications while we shape the brief.',
                'communication_style' => Idea::COMMUNICATION_STYLE_GITHUB,
                'communication_note' => 'Issues and pull requests first.',
                'getting_started_notes' => 'Private setup lives here for accepted collaborators.',
            ]))
            ->assertRedirect();

        $idea = Idea::query()->where('title', 'A useful collaboration tool')->firstOrFail();

        $this->assertSame(Idea::COLLABORATION_STAGE_READY_TO_BUILD, $idea->collaboration_stage);
        $this->assertSame([Idea::HELP_FRONTEND, Idea::HELP_BACKEND], $idea->help_wanted);
        $this->assertFalse($idea->applications_open);
        $this->assertSame(Idea::COMMUNICATION_STYLE_GITHUB, $idea->communication_style);
        $this->assertNotNull($idea->getting_started_notes_updated_at);
    }

    public function testExistingIdeasExposeFriendlyCollaborationDefaults(): void
    {
        $idea = Idea::factory()->create([
            'collaboration_stage' => null,
            'help_wanted' => null,
            'first_contribution' => null,
            'applications_open' => true,
            'communication_style' => null,
            'getting_started_notes' => null,
        ]);

        $props = app(PagePropsService::class)->idea($idea);

        $this->assertSame('Not decided yet', $props['collaboration']['stageDisplay']);
        $this->assertSame([], $props['collaboration']['helpWanted']);
        $this->assertTrue($props['collaboration']['applicationsOpen']);
        $this->assertSame('Not decided yet', $props['collaboration']['communicationStyleDisplay']);
        $this->assertFalse($props['collaboration']['readinessBadges']['firstStepListed']);
        $this->assertFalse($props['collaboration']['readinessBadges']['startNotesReady']);
    }

    public function testPrivateGettingStartedNotesAreOnlyVisibleToOwnerAndActiveCollaborator(): void
    {
        $owner = User::factory()->create();
        $collaborator = User::factory()->create();
        $pendingApplicant = User::factory()->create();
        $leftCollaborator = User::factory()->create();
        $removedCollaborator = User::factory()->create();
        $guest = User::factory()->create();
        $idea = Idea::factory()
            ->for($owner, 'user')
            ->create([
                'getting_started_notes' => '**Private** setup notes.',
                'getting_started_notes_updated_at' => now(),
            ]);

        IdeaApplication::factory()
            ->for($idea, 'idea')
            ->for($collaborator, 'user')
            ->create([
                'status' => IdeaApplication::STATUS_APPROVED,
            ]);
        IdeaApplication::factory()
            ->for($idea, 'idea')
            ->for($pendingApplicant, 'user')
            ->create([
                'status' => IdeaApplication::STATUS_PENDING,
            ]);
        IdeaApplication::factory()
            ->for($idea, 'idea')
            ->for($leftCollaborator, 'user')
            ->create([
                'status' => IdeaApplication::STATUS_LEFT,
            ]);
        IdeaApplication::factory()
            ->for($idea, 'idea')
            ->for($removedCollaborator, 'user')
            ->create([
                'status' => IdeaApplication::STATUS_REMOVED,
            ]);

        $this->assertNull(app(PagePropsService::class)->idea($idea)['collaboration']['gettingStartedNotes']);
        $this->assertNull(app(PagePropsService::class)->idea($idea)['collaboration']['gettingStartedNotesUpdatedAtForHumans']);

        $this->actingAs($guest);
        $this->assertNull(app(PagePropsService::class)->idea($idea)['collaboration']['gettingStartedNotes']);

        $this->actingAs($pendingApplicant);
        $this->assertNull(app(PagePropsService::class)->idea($idea)['collaboration']['gettingStartedNotes']);

        $this->actingAs($leftCollaborator);
        $this->assertNull(app(PagePropsService::class)->idea($idea)['collaboration']['gettingStartedNotes']);

        $this->actingAs($removedCollaborator);
        $this->assertNull(app(PagePropsService::class)->idea($idea)['collaboration']['gettingStartedNotes']);

        $this->actingAs($collaborator);
        $collaboratorProps = app(PagePropsService::class)->idea($idea);

        $this->assertSame('**Private** setup notes.', $collaboratorProps['collaboration']['gettingStartedNotes']);
        $this->assertSame("<p><strong>Private</strong> setup notes.</p>\n", $collaboratorProps['collaboration']['gettingStartedNotesHtml']);
        $this->assertNotNull($collaboratorProps['collaboration']['gettingStartedNotesUpdatedAtForHumans']);

        $this->actingAs($owner);
        $ownerProps = app(PagePropsService::class)->idea($idea);

        $this->assertSame('**Private** setup notes.', $ownerProps['collaboration']['gettingStartedNotes']);
    }

    public function testClosedApplicationsBlockNewApplicationsOnly(): void
    {
        $owner = User::factory()->create();
        $applicant = User::factory()->create();
        $commenter = User::factory()->create();
        $supporter = User::factory()->create();
        $idea = Idea::factory()
            ->for($owner, 'user')
            ->create([
                'applications_open' => false,
            ]);

        $this
            ->actingAs($applicant)
            ->post(route('ideas.applications.store', $idea), [
                'contribution_type' => Idea::HELP_FEEDBACK,
                'first_action' => 'Review the first pass.',
                'content' => 'I can help with a first pass.',
            ])
            ->assertForbidden();

        $this
            ->actingAs($commenter)
            ->post(route('ideas.comments.store', $idea), [
                'content' => 'Public questions still work.',
            ])
            ->assertRedirect(route('ideas.show', $idea));

        $this
            ->actingAs($supporter)
            ->post(route('ideas.supporters.store', $idea))
            ->assertRedirect();

        $this->assertDatabaseMissing('idea_applications', [
            'idea_id' => $idea->id,
            'user_id' => $applicant->id,
        ]);
        $this->assertDatabaseHas('idea_comments', [
            'idea_id' => $idea->id,
            'user_id' => $commenter->id,
            'content' => 'Public questions still work.',
        ]);
        $this->assertDatabaseHas('idea_supporters', [
            'idea_id' => $idea->id,
            'user_id' => $supporter->id,
        ]);
    }

    public function testApplicationStoresIntentFields(): void
    {
        $owner = User::factory()->create();
        $applicant = User::factory()->create();
        $idea = Idea::factory()->for($owner, 'user')->create();

        $this
            ->actingAs($applicant)
            ->post(route('ideas.applications.store', $idea), [
                'contribution_type' => Idea::HELP_TESTING,
                'first_action' => 'I can write the first regression test.',
                'content' => 'I have time this week to help.',
            ])
            ->assertRedirect(route('ideas.show', $idea));

        $this->assertDatabaseHas('idea_applications', [
            'idea_id' => $idea->id,
            'user_id' => $applicant->id,
            'contribution_type' => Idea::HELP_TESTING,
            'first_action' => 'I can write the first regression test.',
            'status' => IdeaApplication::STATUS_PENDING,
        ]);
    }

    public function testDeclinedAndWithdrawnApplicationsDoNotBlockReapplying(): void
    {
        $owner = User::factory()->create();
        $applicant = User::factory()->create();
        $idea = Idea::factory()->for($owner, 'user')->create();

        foreach ([IdeaApplication::STATUS_DECLINED, IdeaApplication::STATUS_WITHDRAWN] as $status) {
            IdeaApplication::factory()
                ->for($idea, 'idea')
                ->for($applicant, 'user')
                ->create([
                    'status' => $status,
                ]);
        }

        $this
            ->actingAs($applicant)
            ->post(route('ideas.applications.store', $idea), [
                'contribution_type' => Idea::HELP_TESTING,
                'first_action' => 'Review the first public step.',
                'content' => 'I can apply again after a final state.',
            ])
            ->assertRedirect(route('ideas.show', $idea));

        $this->assertDatabaseCount('idea_applications', 3);
        $this->assertDatabaseHas('idea_applications', [
            'idea_id' => $idea->id,
            'user_id' => $applicant->id,
            'status' => IdeaApplication::STATUS_PENDING,
        ]);
    }

    public function testDecliningOrRemovingKeepsHistoricalApplications(): void
    {
        $owner = User::factory()->create();
        $pendingApplicant = User::factory()->create();
        $collaborator = User::factory()->create();
        $idea = Idea::factory()->for($owner, 'user')->create();
        $pendingApplication = IdeaApplication::factory()
            ->for($idea, 'idea')
            ->for($pendingApplicant, 'user')
            ->create([
                'status' => IdeaApplication::STATUS_PENDING,
            ]);
        $approvedApplication = IdeaApplication::factory()
            ->for($idea, 'idea')
            ->for($collaborator, 'user')
            ->create([
                'status' => IdeaApplication::STATUS_APPROVED,
            ]);

        $this
            ->actingAs($owner)
            ->delete(route('ideas.applications.destroy', [$idea, $pendingApplication]))
            ->assertRedirect();

        $this
            ->actingAs($owner)
            ->delete(route('ideas.applications.destroy', [$idea, $approvedApplication]))
            ->assertRedirect();

        $this->assertDatabaseHas('idea_applications', [
            'id' => $pendingApplication->id,
            'status' => IdeaApplication::STATUS_DECLINED,
        ]);
        $this->assertDatabaseHas('idea_applications', [
            'id' => $approvedApplication->id,
            'status' => IdeaApplication::STATUS_REMOVED,
        ]);
        $this->assertDatabaseHas('idea_application_messages', [
            'idea_application_id' => $pendingApplication->id,
            'type' => IdeaApplicationMessage::TYPE_DECLINED,
        ]);
        $this->assertDatabaseHas('idea_application_messages', [
            'idea_application_id' => $approvedApplication->id,
            'type' => IdeaApplicationMessage::TYPE_REMOVED,
        ]);

        $this
            ->actingAs($owner)
            ->get(route('ideas.dashboard', $idea))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Ideas/Manage')
                ->has('applications.items', 0)
                ->has('collaborators.items', 0));
    }

    public function testApplicationReadStateIsUniquePerUser(): void
    {
        $application = IdeaApplication::factory()->create();
        $user = User::factory()->create();

        IdeaApplicationReadState::factory()
            ->for($application, 'application')
            ->for($user, 'user')
            ->create();

        $this->expectException(QueryException::class);

        IdeaApplicationReadState::factory()
            ->for($application, 'application')
            ->for($user, 'user')
            ->create();
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
}
