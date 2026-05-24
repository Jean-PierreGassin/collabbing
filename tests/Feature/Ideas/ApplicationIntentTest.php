<?php

namespace Tests\Feature\Ideas;

use App\Models\Idea;
use App\Models\IdeaApplication;
use App\Models\IdeaApplicationMessage;
use App\Models\User;
use App\Notifications\Ideas\NewIdeaApplicationNotification;
use App\Services\Inertia\PagePropsService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ApplicationIntentTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function testApplicationCanBeSubmittedWithIntentAndOptionalContext(): void
    {
        Notification::fake();

        $owner = User::factory()->create();
        $applicant = User::factory()->create();
        $idea = Idea::factory()->for($owner, 'user')->create([
            'help_wanted' => [
                Idea::HELP_FRONTEND,
                Idea::HELP_TESTING,
            ],
        ]);

        $this
            ->actingAs($applicant)
            ->post(route('ideas.applications.store', $idea), [
                'contribution_type' => Idea::HELP_FRONTEND,
                'first_action' => 'Open a small pull request for the responsive card state.',
                'content' => null,
            ])
            ->assertRedirect(route('ideas.show', $idea));

        $application = IdeaApplication::query()
            ->where('idea_id', $idea->id)
            ->where('user_id', $applicant->id)
            ->firstOrFail();

        $this->assertSame(Idea::HELP_FRONTEND, $application->contribution_type);
        $this->assertSame('Open a small pull request for the responsive card state.', $application->first_action);
        $this->assertSame('', $application->content);

        Notification::assertSentTo($owner, NewIdeaApplicationNotification::class);
        Notification::assertNotSentTo($applicant, NewIdeaApplicationNotification::class);
    }

    public function testOwnerEmailIncludesIntentNotApplicationMessage(): void
    {
        Notification::fake();

        $owner = User::factory()->create();
        $applicant = User::factory()->create([
            'first_name' => 'Applied',
            'last_name' => 'User',
        ]);
        $idea = Idea::factory()->for($owner, 'user')->create();

        $this
            ->actingAs($applicant)
            ->post(route('ideas.applications.store', $idea), [
                'contribution_type' => Idea::HELP_BACKEND,
                'first_action' => 'Review the API route shape.',
                'content' => 'This private application message should stay out of the email.',
            ])
            ->assertRedirect(route('ideas.show', $idea));

        Notification::assertSentTo(
            $owner,
            NewIdeaApplicationNotification::class,
            function (NewIdeaApplicationNotification $notification, array $channels) use ($owner): bool {
                $mail = $notification->toMail($owner);
                $lines = implode(' ', [
                    ...$mail->introLines,
                    ...$mail->outroLines,
                ]);

                return in_array('mail', $channels, true)
                    && $mail->subject === 'New application for '.$notification->idea->title
                    && str_contains($lines, 'Applied User')
                    && str_contains($lines, 'Backend')
                    && str_contains($lines, 'Review the API route shape.')
                    && ! str_contains($lines, 'private application message');
            }
        );
    }

    public function testApplicationRequiresContributionTypeAndFirstAction(): void
    {
        $owner = User::factory()->create();
        $applicant = User::factory()->create();
        $idea = Idea::factory()->for($owner, 'user')->create();

        $this
            ->actingAs($applicant)
            ->post(route('ideas.applications.store', $idea), [
                'content' => 'I can help.',
            ])
            ->assertSessionHasErrors([
                'contribution_type',
                'first_action',
            ]);

        $this->assertDatabaseCount('idea_applications', 0);
    }

    #[DataProvider('invalidApplicationIntentPayloads')]
    public function testApplicationIntentRejectsInvalidInput(array $overrides, string $errorKey): void
    {
        $owner = User::factory()->create();
        $applicant = User::factory()->create();
        $idea = Idea::factory()->for($owner, 'user')->create();

        $this
            ->actingAs($applicant)
            ->post(route('ideas.applications.store', $idea), [
                'contribution_type' => Idea::HELP_FRONTEND,
                'first_action' => 'Review the first public step.',
                'content' => 'Useful context.',
                ...$overrides,
            ])
            ->assertSessionHasErrors($errorKey);

        $this->assertDatabaseCount('idea_applications', 0);
    }

    public function testPendingApplicantCanEditApplication(): void
    {
        $owner = User::factory()->create();
        $applicant = User::factory()->create();
        $idea = Idea::factory()->for($owner, 'user')->create();
        $application = IdeaApplication::factory()
            ->for($idea, 'idea')
            ->for($applicant, 'user')
            ->create([
                'contribution_type' => Idea::HELP_FRONTEND,
                'first_action' => 'Original first action.',
                'content' => 'Original message.',
                'status' => IdeaApplication::STATUS_PENDING,
            ]);

        $this
            ->actingAs($applicant)
            ->get(route('ideas.applications.edit', [$idea, $application]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Ideas/Apply')
                ->where('application.id', $application->id)
                ->where('application.contributionType', Idea::HELP_FRONTEND));

        $this
            ->actingAs($applicant)
            ->put(route('ideas.applications.update', [$idea, $application]), [
                'contribution_type' => Idea::HELP_TESTING,
                'first_action' => 'Write a focused regression test.',
                'content' => 'Updated context.',
            ])
            ->assertRedirect(route('ideas.show', $idea));

        $this->assertDatabaseHas('idea_applications', [
            'id' => $application->id,
            'contribution_type' => Idea::HELP_TESTING,
            'first_action' => 'Write a focused regression test.',
            'content' => 'Updated context.',
        ]);
    }

    public function testFinalApplicationCannotBeEdited(): void
    {
        $owner = User::factory()->create();
        $applicant = User::factory()->create();
        $idea = Idea::factory()->for($owner, 'user')->create();
        $application = IdeaApplication::factory()
            ->for($idea, 'idea')
            ->for($applicant, 'user')
            ->create([
                'status' => IdeaApplication::STATUS_DECLINED,
            ]);

        $this
            ->actingAs($applicant)
            ->put(route('ideas.applications.update', [$idea, $application]), [
                'contribution_type' => Idea::HELP_TESTING,
                'first_action' => 'Try again.',
                'content' => 'This should not update.',
            ])
            ->assertForbidden();
    }

    public function testClosedApplicationsDoNotBlockPendingEditOrWithdraw(): void
    {
        $owner = User::factory()->create();
        $applicant = User::factory()->create();
        $idea = Idea::factory()->for($owner, 'user')->create([
            'applications_open' => false,
        ]);
        $application = IdeaApplication::factory()
            ->for($idea, 'idea')
            ->for($applicant, 'user')
            ->create([
                'status' => IdeaApplication::STATUS_PENDING,
            ]);

        $this
            ->actingAs($applicant)
            ->put(route('ideas.applications.update', [$idea, $application]), [
                'contribution_type' => Idea::HELP_DESIGN,
                'first_action' => 'Review the collaboration copy.',
                'content' => 'Still interested while applications are paused.',
            ])
            ->assertRedirect(route('ideas.show', $idea));

        $this
            ->actingAs($applicant)
            ->delete(route('ideas.applications.destroy', [$idea, $application]))
            ->assertRedirect(route('ideas.show', $idea));

        $this->assertDatabaseHas('idea_applications', [
            'id' => $application->id,
            'status' => IdeaApplication::STATUS_WITHDRAWN,
        ]);
    }

    public function testPendingApplicationCountIsOwnerOnly(): void
    {
        $owner = User::factory()->create();
        $guest = User::factory()->create();
        $idea = Idea::factory()->for($owner, 'user')->create();

        IdeaApplication::factory()
            ->for($idea, 'idea')
            ->for($guest, 'user')
            ->create([
                'status' => IdeaApplication::STATUS_PENDING,
            ]);

        $publicProps = app(PagePropsService::class)->idea($idea);
        $this->assertNull($publicProps['pendingApplicationsCount']);

        $this->actingAs($owner);

        $ownerProps = app(PagePropsService::class)->idea($idea);

        $this->assertSame(1, $ownerProps['pendingApplicationsCount']);
    }

    public function testPendingApplicantCanWithdrawAndReapply(): void
    {
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
            ->assertRedirect(route('ideas.show', $idea));

        $this->assertDatabaseHas('idea_applications', [
            'id' => $application->id,
            'status' => IdeaApplication::STATUS_WITHDRAWN,
        ]);

        $withdrawnAt = $application->fresh()?->getAttributeValue('withdrawn_at');

        $this->assertNotNull($withdrawnAt);
        $this->assertDatabaseHas('idea_application_messages', [
            'idea_application_id' => $application->id,
            'type' => IdeaApplicationMessage::TYPE_WITHDRAWN,
        ]);

        $this
            ->actingAs($applicant)
            ->post(route('ideas.applications.store', $idea), [
                'contribution_type' => Idea::HELP_FEEDBACK,
                'first_action' => 'Review the public first step.',
                'content' => '',
            ])
            ->assertRedirect(route('ideas.show', $idea));

        $this->assertDatabaseCount('idea_applications', 2);
    }

    public static function invalidApplicationIntentPayloads(): array
    {
        return [
            'unknown contribution type' => [
                ['contribution_type' => 'finance'],
                'contribution_type',
            ],
            'long first action' => [
                ['first_action' => str_repeat('a', 281)],
                'first_action',
            ],
            'long context' => [
                ['content' => str_repeat('a', 1501)],
                'content',
            ],
        ];
    }
}
