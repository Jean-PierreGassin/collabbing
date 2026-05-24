<?php

namespace Tests\Feature\Ideas;

use App\Models\CodeRepository;
use App\Models\Idea;
use App\Models\IdeaApplication;
use App\Models\User;
use App\Notifications\Ideas\IdeaApplicationApprovedNotification;
use App\Notifications\Ideas\IdeaApplicationDeclinedNotification;
use App\Services\Inertia\PagePropsService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ApplicationDecisionTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function testOwnerApprovesWithNote(): void
    {
        Notification::fake();

        [
            'owner' => $owner,
            'applicant' => $applicant,
            'idea' => $idea,
            'application' => $application,
        ] = $this->pendingApplication();

        $this
            ->actingAs($owner)
            ->put(route('ideas.applications.approve', [$idea, $application]), [
                'approval_note' => 'Start with the private onboarding checklist.',
            ])
            ->assertRedirect()
            ->assertSessionHas('status', 'You have approved '.$applicant->name)
            ->assertSessionMissing('repositoryInvitePrompt');

        $this->assertDatabaseHas('idea_applications', [
            'id' => $application->id,
            'approval_note' => 'Start with the private onboarding checklist.',
            'status' => IdeaApplication::STATUS_APPROVED,
        ]);

        Notification::assertSentTo(
            $applicant,
            IdeaApplicationApprovedNotification::class,
            function (IdeaApplicationApprovedNotification $notification) use ($applicant): bool {
                $mail = $notification->toMail($applicant);
                $lines = implode(' ', [
                    ...$mail->introLines,
                    ...$mail->outroLines,
                ]);

                return $mail->subject === 'Application accepted for '.$notification->idea->title
                    && str_contains($lines, 'was accepted')
                    && ! str_contains($lines, 'private onboarding checklist');
            }
        );
    }

    public function testOwnerDeclinesWithReason(): void
    {
        Notification::fake();

        [
            'owner' => $owner,
            'applicant' => $applicant,
            'idea' => $idea,
            'application' => $application,
        ] = $this->pendingApplication();

        $this
            ->actingAs($owner)
            ->delete(route('ideas.applications.destroy', [$idea, $application]), [
                'decline_reason' => 'The scope needs a different skill set right now.',
            ])
            ->assertRedirect()
            ->assertSessionHas('status', $applicant->name.' has been declined.');

        $this->assertDatabaseHas('idea_applications', [
            'id' => $application->id,
            'decline_reason' => 'The scope needs a different skill set right now.',
            'status' => IdeaApplication::STATUS_DECLINED,
        ]);

        Notification::assertSentTo(
            $applicant,
            IdeaApplicationDeclinedNotification::class,
            function (IdeaApplicationDeclinedNotification $notification) use ($applicant): bool {
                $mail = $notification->toMail($applicant);
                $lines = implode(' ', [
                    ...$mail->introLines,
                    ...$mail->outroLines,
                ]);

                return $mail->subject === 'Application update for '.$notification->idea->title
                    && str_contains($lines, 'was declined')
                    && str_contains($lines, 'different skill set');
            }
        );
        Notification::assertNotSentTo($applicant, IdeaApplicationApprovedNotification::class);
    }

    public function testDeclineReasonIsOptional(): void
    {
        Notification::fake();

        [
            'owner' => $owner,
            'applicant' => $applicant,
            'idea' => $idea,
            'application' => $application,
        ] = $this->pendingApplication();

        $this
            ->actingAs($owner)
            ->delete(route('ideas.applications.destroy', [$idea, $application]))
            ->assertRedirect();

        $this->assertDatabaseHas('idea_applications', [
            'id' => $application->id,
            'decline_reason' => null,
            'status' => IdeaApplication::STATUS_DECLINED,
        ]);

        Notification::assertSentTo(
            $applicant,
            IdeaApplicationDeclinedNotification::class,
            function (IdeaApplicationDeclinedNotification $notification) use ($applicant): bool {
                $mail = $notification->toMail($applicant);
                $lines = implode(' ', [
                    ...$mail->introLines,
                    ...$mail->outroLines,
                ]);

                return ! str_contains($lines, 'Reason:');
            }
        );
    }

    public function testDecisionNotesAreValidated(): void
    {
        [
            'owner' => $owner,
            'idea' => $idea,
            'application' => $application,
        ] = $this->pendingApplication();

        $this
            ->actingAs($owner)
            ->put(route('ideas.applications.approve', [$idea, $application]), [
                'approval_note' => str_repeat('a', 1201),
            ])
            ->assertSessionHasErrors('approval_note');

        $this
            ->actingAs($owner)
            ->put(route('ideas.applications.approve', [$idea, $application]), [
                'decline_reason' => 'Wrong route.',
            ])
            ->assertSessionHasErrors('decline_reason');

        $this
            ->actingAs($owner)
            ->delete(route('ideas.applications.destroy', [$idea, $application]), [
                'decline_reason' => str_repeat('a', 1201),
            ])
            ->assertSessionHasErrors('decline_reason');

        $this
            ->actingAs($owner)
            ->delete(route('ideas.applications.destroy', [$idea, $application]), [
                'approval_note' => 'Wrong route.',
            ])
            ->assertSessionHasErrors('approval_note');
    }

    public function testFinalDecisionsCannotBeRepeated(): void
    {
        [
            'owner' => $owner,
            'idea' => $idea,
            'application' => $application,
        ] = $this->pendingApplication();

        $application->forceFill([
            'status' => IdeaApplication::STATUS_DECLINED,
        ])->save();

        $this
            ->actingAs($owner)
            ->put(route('ideas.applications.approve', [$idea, $application]))
            ->assertForbidden();

        $this
            ->actingAs($owner)
            ->delete(route('ideas.applications.destroy', [$idea, $application]))
            ->assertForbidden();

        $this->assertDatabaseHas('idea_applications', [
            'id' => $application->id,
            'status' => IdeaApplication::STATUS_DECLINED,
        ]);
    }

    public function testDecisionNotesStayPrivate(): void
    {
        [
            'owner' => $owner,
            'applicant' => $applicant,
            'idea' => $idea,
            'application' => $application,
        ] = $this->pendingApplication();

        $this
            ->actingAs($owner)
            ->put(route('ideas.applications.approve', [$idea, $application]), [
                'approval_note' => 'Start with a private checklist.',
            ])
            ->assertRedirect();

        $freshApplication = $application->fresh();

        $this->assertInstanceOf(IdeaApplication::class, $freshApplication);

        $this->actingAs($owner);
        $ownerProps = app(PagePropsService::class)->application($freshApplication);

        $this->actingAs($applicant);
        $applicantProps = app(PagePropsService::class)->application($freshApplication);

        $this->actingAs(User::factory()->create());
        $outsiderProps = app(PagePropsService::class)->application($freshApplication);

        $this->assertSame('Start with a private checklist.', $ownerProps['approvalNote']);
        $this->assertSame('Start with a private checklist.', $applicantProps['approvalNote']);
        $this->assertNull($outsiderProps['approvalNote']);
    }

    public function testRepositoryPromptAppearsWithAvailableRepository(): void
    {
        [
            'owner' => $owner,
            'idea' => $idea,
            'application' => $application,
        ] = $this->pendingApplication(ownerHasGithubToken: true);

        $this->activeRepositoryFor($idea);

        $this
            ->actingAs($owner)
            ->put(route('ideas.applications.approve', [$idea, $application]))
            ->assertRedirect()
            ->assertSessionHas('repositoryInvitePrompt', true);
    }

    public function testRepositoryPromptSkipsUnavailableRepository(): void
    {
        [
            'owner' => $owner,
            'idea' => $idea,
            'application' => $application,
        ] = $this->pendingApplication(ownerHasGithubToken: true);

        $this
            ->actingAs($owner)
            ->put(route('ideas.applications.approve', [$idea, $application]))
            ->assertRedirect()
            ->assertSessionMissing('repositoryInvitePrompt');
    }

    private function pendingApplication(bool $ownerHasGithubToken = false): array
    {
        $ownerFactory = User::factory();

        if ($ownerHasGithubToken) {
            $ownerFactory = $ownerFactory->withGithubAccount('owner-token', 'thread-owner');
        }

        $owner = $ownerFactory->create();
        $applicant = User::factory()->create();
        $idea = Idea::factory()->for($owner, 'user')->create();
        $application = IdeaApplication::factory()
            ->for($idea, 'idea')
            ->for($applicant, 'user')
            ->create([
                'status' => IdeaApplication::STATUS_PENDING,
            ]);

        return [
            'owner' => $owner,
            'applicant' => $applicant,
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
