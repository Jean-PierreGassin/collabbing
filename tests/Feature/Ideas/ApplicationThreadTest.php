<?php

namespace Tests\Feature\Ideas;

use App\Models\Idea;
use App\Models\IdeaApplication;
use App\Models\IdeaApplicationMessage;
use App\Models\User;
use App\Notifications\Ideas\IdeaApplicationThreadMessageNotification;
use App\Services\Inertia\PagePropsService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ApplicationThreadTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function testApplicantCanMessageOwnerInPendingThread(): void
    {
        Notification::fake();

        [
            'owner' => $owner,
            'applicant' => $applicant,
            'idea' => $idea,
            'application' => $application,
        ] = $this->pendingApplication();

        $this
            ->actingAs($applicant)
            ->post(route('ideas.applications.messages.store', [$idea, $application]), [
                'body' => 'Could you point me at the first useful issue?',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('idea_application_messages', [
            'idea_application_id' => $application->id,
            'user_id' => $applicant->id,
            'type' => IdeaApplicationMessage::TYPE_MESSAGE,
            'body' => 'Could you point me at the first useful issue?',
        ]);
        $this->assertDatabaseHas('idea_application_read_states', [
            'idea_application_id' => $application->id,
            'user_id' => $applicant->id,
        ]);

        Notification::assertSentTo($owner, IdeaApplicationThreadMessageNotification::class);
        Notification::assertNotSentTo($applicant, IdeaApplicationThreadMessageNotification::class);
    }

    public function testOwnerCanMessageApplicantInPendingThread(): void
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
            ->post(route('ideas.applications.messages.store', [$idea, $application]), [
                'body' => 'Can you start with the API route shape?',
            ])
            ->assertRedirect();

        Notification::assertSentTo($applicant, IdeaApplicationThreadMessageNotification::class);
        Notification::assertNotSentTo($owner, IdeaApplicationThreadMessageNotification::class);
    }

    public function testOnlyParticipantsCanUseApplicationThread(): void
    {
        [
            'idea' => $idea,
            'application' => $application,
        ] = $this->pendingApplication();
        $outsider = User::factory()->create();

        $this
            ->actingAs($outsider)
            ->post(route('ideas.applications.messages.store', [$idea, $application]), [
                'body' => 'Let me in.',
            ])
            ->assertForbidden();

        $this->actingAs($outsider);

        $props = app(PagePropsService::class)->application($application);

        $this->assertNull($props['thread']);
    }

    public function testThreadRoutesCannotCrossIdeas(): void
    {
        [
            'applicant' => $applicant,
            'application' => $application,
        ] = $this->pendingApplication();
        $otherIdea = Idea::factory()->for(User::factory(), 'user')->create();

        $this
            ->actingAs($applicant)
            ->post(route('ideas.applications.messages.store', [$otherIdea, $application]), [
                'body' => 'Wrong idea.',
            ])
            ->assertNotFound();
    }

    public function testFinalThreadsAreVisibleButReadOnly(): void
    {
        [
            'applicant' => $applicant,
            'idea' => $idea,
            'application' => $application,
        ] = $this->pendingApplication([
            'status' => IdeaApplication::STATUS_DECLINED,
        ]);

        IdeaApplicationMessage::factory()
            ->for($application, 'application')
            ->for($applicant, 'user')
            ->create([
                'body' => 'Thanks for reviewing.',
            ]);

        $this
            ->actingAs($applicant)
            ->post(route('ideas.applications.messages.store', [$idea, $application]), [
                'body' => 'Can I still reply?',
            ])
            ->assertForbidden();

        $this->actingAs($applicant);

        $props = app(PagePropsService::class)->application($application);

        $this->assertTrue($props['thread']['isReadOnly']);
        $this->assertFalse($props['thread']['canMessage']);
        $this->assertSame('Thanks for reviewing.', $props['thread']['messages'][0]['body']);
    }

    public function testThreadReadStateTracksUnreadMessages(): void
    {
        [
            'owner' => $owner,
            'applicant' => $applicant,
            'application' => $application,
        ] = $this->pendingApplication();

        IdeaApplicationMessage::factory()
            ->for($application, 'application')
            ->for($applicant, 'user')
            ->create([
                'body' => 'I added a question.',
            ]);

        $this->actingAs($owner);

        $props = app(PagePropsService::class)->application($application);

        $this->assertTrue($props['thread']['hasUnread']);
        $this->assertSame(1, $props['thread']['unreadCount']);

        $this
            ->put(route('ideas.applications.read-state.update', [$application->idea, $application]))
            ->assertNoContent();

        $freshProps = app(PagePropsService::class)->application($application);

        $this->assertFalse($freshProps['thread']['hasUnread']);
        $this->assertSame(0, $freshProps['thread']['unreadCount']);
    }

    public function testLifecycleMessagesAreUnreadForOtherParticipant(): void
    {
        [
            'owner' => $owner,
            'applicant' => $applicant,
            'idea' => $idea,
            'application' => $application,
        ] = $this->pendingApplication();

        $this
            ->actingAs($owner)
            ->put(route('ideas.applications.approve', [$idea, $application]))
            ->assertRedirect();

        $freshApplication = $application->fresh();

        $this->assertInstanceOf(IdeaApplication::class, $freshApplication);

        $ownerProps = app(PagePropsService::class)->application($freshApplication);

        $this->assertFalse($ownerProps['thread']['hasUnread']);

        $this->actingAs($applicant);

        $applicantProps = app(PagePropsService::class)->application($freshApplication);

        $this->assertTrue($applicantProps['thread']['hasUnread']);
        $this->assertSame(1, $applicantProps['thread']['unreadCount']);
    }

    public function testThreadEmailsAreThrottled(): void
    {
        Notification::fake();
        Cache::flush();

        [
            'owner' => $owner,
            'applicant' => $applicant,
            'idea' => $idea,
            'application' => $application,
        ] = $this->pendingApplication();

        $this
            ->actingAs($applicant)
            ->post(route('ideas.applications.messages.store', [$idea, $application]), [
                'body' => 'First question.',
            ])
            ->assertRedirect();

        $this
            ->actingAs($applicant)
            ->post(route('ideas.applications.messages.store', [$idea, $application]), [
                'body' => 'Second question before the owner can reply.',
            ])
            ->assertRedirect();

        $this->assertDatabaseCount('idea_application_messages', 2);

        Notification::assertSentToTimes($owner, IdeaApplicationThreadMessageNotification::class, 1);
    }

    public function testThreadMessageRequiresBody(): void
    {
        [
            'applicant' => $applicant,
            'idea' => $idea,
            'application' => $application,
        ] = $this->pendingApplication();

        $this
            ->actingAs($applicant)
            ->post(route('ideas.applications.messages.store', [$idea, $application]), [
                'body' => '',
            ])
            ->assertSessionHasErrors('body');
    }

    public function testThreadMessageRejectsWhitespaceBody(): void
    {
        [
            'applicant' => $applicant,
            'idea' => $idea,
            'application' => $application,
        ] = $this->pendingApplication();

        $this
            ->actingAs($applicant)
            ->post(route('ideas.applications.messages.store', [$idea, $application]), [
                'body' => '    ',
            ])
            ->assertSessionHasErrors('body');
    }

    private function pendingApplication(array $applicationOverrides = []): array
    {
        $owner = User::factory()->create();
        $applicant = User::factory()->create();
        $idea = Idea::factory()->for($owner, 'user')->create();
        $application = IdeaApplication::factory()
            ->for($idea, 'idea')
            ->for($applicant, 'user')
            ->create([
                'status' => IdeaApplication::STATUS_PENDING,
                ...$applicationOverrides,
            ]);

        return [
            'owner' => $owner,
            'applicant' => $applicant,
            'idea' => $idea,
            'application' => $application,
        ];
    }
}
