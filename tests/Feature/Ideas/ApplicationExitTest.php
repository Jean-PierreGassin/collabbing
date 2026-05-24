<?php

namespace Tests\Feature\Ideas;

use App\Models\CodeRepository;
use App\Models\Idea;
use App\Models\IdeaApplication;
use App\Models\IdeaApplicationMessage;
use App\Models\User;
use App\Notifications\Ideas\IdeaApplicationWithdrawnNotification;
use App\Notifications\Ideas\IdeaCollaboratorLeftNotification;
use App\Notifications\Ideas\IdeaCollaboratorRemovedNotification;
use App\Services\Inertia\PagePropsService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ApplicationExitTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function testCollaboratorCanLeaveWithReason(): void
    {
        Notification::fake();

        [
            'owner' => $owner,
            'collaborator' => $collaborator,
            'idea' => $idea,
            'application' => $application,
        ] = $this->approvedApplication();

        $this->activeRepositoryFor($idea);

        $this
            ->actingAs($collaborator)
            ->delete(route('ideas.applications.destroy', [$idea, $application]), [
                'exit_reason' => 'I need to step away from this work.',
            ])
            ->assertRedirect(route('ideas.show', $idea->id))
            ->assertSessionHas('status', 'You have left this collaboration.');

        $this->assertDatabaseHas('idea_applications', [
            'id' => $application->id,
            'status' => IdeaApplication::STATUS_LEFT,
        ]);
        $this->assertDatabaseHas('idea_application_messages', [
            'idea_application_id' => $application->id,
            'type' => IdeaApplicationMessage::TYPE_LEFT,
            'body' => 'I need to step away from this work.',
        ]);
        $this->assertNotNull($application->fresh()?->getAttributeValue('left_at'));

        Notification::assertSentTo(
            $owner,
            IdeaCollaboratorLeftNotification::class,
            function (IdeaCollaboratorLeftNotification $notification) use ($owner): bool {
                $mail = $notification->toMail($owner);
                $lines = implode(' ', [
                    ...$mail->introLines,
                    ...$mail->outroLines,
                ]);

                return $notification->shouldReviewRepositoryAccess
                    && str_contains($lines, 'step away')
                    && str_contains($lines, 'Review repository access');
            }
        );
    }

    public function testOwnerCanRemoveCollaboratorWithReason(): void
    {
        Notification::fake();

        [
            'owner' => $owner,
            'collaborator' => $collaborator,
            'idea' => $idea,
            'application' => $application,
        ] = $this->approvedApplication();

        $this->activeRepositoryFor($idea);

        $this
            ->actingAs($owner)
            ->delete(route('ideas.applications.destroy', [$idea, $application]), [
                'exit_reason' => 'The collaboration no longer fits the project.',
            ])
            ->assertRedirect()
            ->assertSessionHas('status', $collaborator->name.' has been removed from this idea.')
            ->assertSessionHas('repositoryAccessPrompt', true);

        $this->assertDatabaseHas('idea_applications', [
            'id' => $application->id,
            'status' => IdeaApplication::STATUS_REMOVED,
        ]);
        $this->assertDatabaseHas('idea_application_messages', [
            'idea_application_id' => $application->id,
            'type' => IdeaApplicationMessage::TYPE_REMOVED,
            'body' => 'The collaboration no longer fits the project.',
        ]);
        $this->assertNotNull($application->fresh()?->getAttributeValue('removed_at'));

        Notification::assertSentTo(
            $collaborator,
            IdeaCollaboratorRemovedNotification::class,
            function (IdeaCollaboratorRemovedNotification $notification) use ($collaborator): bool {
                $mail = $notification->toMail($collaborator);
                $lines = implode(' ', [
                    ...$mail->introLines,
                    ...$mail->outroLines,
                ]);

                return $notification->shouldReviewRepositoryAccess
                    && str_contains($lines, 'no longer fits')
                    && ! str_contains($lines, 'owner has been prompted');
            }
        );
    }

    public function testOwnerGetsWithdrawalEmail(): void
    {
        Notification::fake();

        $owner = User::factory()->create();
        $applicant = User::factory()->create();
        $idea = Idea::factory()->for($owner, 'user')->create();
        $application = IdeaApplication::factory()
            ->for($idea, 'idea')
            ->for($applicant, 'user')
            ->create([
                'status' => IdeaApplication::STATUS_PENDING,
            ]);

        $this
            ->actingAs($applicant)
            ->delete(route('ideas.applications.destroy', [$idea, $application]))
            ->assertRedirect(route('ideas.show', $idea->id));

        Notification::assertSentTo(
            $owner,
            IdeaApplicationWithdrawnNotification::class,
            function (IdeaApplicationWithdrawnNotification $notification) use ($owner): bool {
                $mail = $notification->toMail($owner);

                return $mail->subject === 'Application withdrawn for '.$notification->idea->title;
            }
        );
    }

    public function testEndedCollaboratorsLosePrivateStartNotesButKeepReadOnlyThread(): void
    {
        [
            'collaborator' => $collaborator,
            'idea' => $idea,
            'application' => $application,
        ] = $this->approvedApplication([
            'getting_started_notes' => 'Private setup links.',
        ], [
            'approval_note' => 'Private approval note.',
        ]);

        $this
            ->actingAs($collaborator)
            ->delete(route('ideas.applications.destroy', [$idea, $application]))
            ->assertRedirect();

        $freshApplication = $application->fresh();

        $this->assertInstanceOf(IdeaApplication::class, $freshApplication);

        $props = app(PagePropsService::class)->idea($idea->fresh());
        $applicationProps = app(PagePropsService::class)->application($freshApplication);

        $this->assertNull($props['collaboration']['gettingStartedNotes']);
        $this->assertNull($applicationProps['approvalNote']);
        $this->assertTrue($applicationProps['thread']['isReadOnly']);

        $this
            ->get(route('ideas.show', $idea))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('historicalApplication.id', $application->id)
                ->where('historicalApplication.thread.isReadOnly', true));
    }

    public function testOwnerSeesRepositoryAccessPromptAfterExit(): void
    {
        [
            'owner' => $owner,
            'collaborator' => $collaborator,
            'idea' => $idea,
            'application' => $application,
        ] = $this->approvedApplication();

        $this->activeRepositoryFor($idea);

        $this
            ->actingAs($collaborator)
            ->delete(route('ideas.applications.destroy', [$idea, $application]))
            ->assertRedirect();

        $this
            ->actingAs($owner)
            ->get(route('ideas.dashboard', $idea))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('idea.repositoryAccessReviewNeeded', true));
    }

    private function approvedApplication(array $ideaOverrides = [], array $applicationOverrides = []): array
    {
        $owner = User::factory()->create();
        $collaborator = User::factory()->create();
        $idea = Idea::factory()
            ->for($owner, 'user')
            ->create($ideaOverrides);
        $application = IdeaApplication::factory()
            ->for($idea, 'idea')
            ->for($collaborator, 'user')
            ->create([
                ...$applicationOverrides,
                'status' => IdeaApplication::STATUS_APPROVED,
            ]);

        return [
            'owner' => $owner,
            'collaborator' => $collaborator,
            'idea' => $idea,
            'application' => $application,
        ];
    }

    private function activeRepositoryFor(Idea $idea): void
    {
        $idea->codeRepository()->create([
            'provider' => CodeRepository::PROVIDER_GITHUB,
            'status' => CodeRepository::STATUS_ACTIVE,
            'owner' => 'thread-owner',
            'name' => 'thread-repo-'.$idea->id,
            'html_url' => 'https://github.com/thread-owner/thread-repo-'.$idea->id,
        ]);
    }
}
