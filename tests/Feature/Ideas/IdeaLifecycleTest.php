<?php

namespace Tests\Feature\Ideas;

use App\Models\Idea;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class IdeaLifecycleTest extends TestCase
{
    use LazilyRefreshDatabase;

    #[DataProvider('ideaStatuses')]
    public function testOwnerCanUpdateIdeaStatus(string $status, string $display): void
    {
        $owner = User::factory()->create();
        $idea = Idea::factory()->for($owner, 'user')->create();

        $this
            ->actingAs($owner)
            ->patch(route('ideas.status.update', $idea), [
                'status' => $status,
            ])
            ->assertRedirect(route('ideas.dashboard', $idea))
            ->assertSessionHas('status', 'Idea status updated.');

        $this->assertDatabaseHas('ideas', [
            'id' => $idea->id,
            'status' => $status,
        ]);

        $this
            ->actingAs($owner)
            ->get(route('ideas.dashboard', $idea))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Ideas/Manage')
                ->where('idea.status', $status)
                ->where('idea.statusDisplay', $display));
    }

    public function testNonOwnerCannotUpdateIdeaStatus(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $idea = Idea::factory()->for($owner, 'user')->create();

        $this
            ->actingAs($otherUser)
            ->patch(route('ideas.status.update', $idea), [
                'status' => Idea::STATUS_CLOSED,
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('ideas', [
            'id' => $idea->id,
            'status' => Idea::STATUS_OPEN,
        ]);
    }

    public function testInvalidIdeaStatusIsRejected(): void
    {
        $owner = User::factory()->create();
        $idea = Idea::factory()->for($owner, 'user')->create();

        $this
            ->actingAs($owner)
            ->patch(route('ideas.status.update', $idea), [
                'status' => 'archived',
            ])
            ->assertSessionHasErrors('status');
    }

    #[DataProvider('inactiveIdeaStatuses')]
    public function testInactiveIdeasDoNotAcceptNewApplicationsOrSupport(string $status): void
    {
        $owner = User::factory()->create();
        $visitor = User::factory()->create();
        $idea = Idea::factory()->for($owner, 'user')->create([
            'status' => $status,
        ]);

        $this
            ->actingAs($visitor)
            ->post(route('ideas.applications.store', $idea), [
                'content' => 'I can help build this.',
            ])
            ->assertForbidden();

        $this
            ->actingAs($visitor)
            ->post(route('ideas.supporters.store', $idea))
            ->assertForbidden();

        $this->assertDatabaseCount('idea_applications', 0);
        $this->assertDatabaseCount('idea_supporters', 0);
    }

    public function testInactiveIdeasStayOutOfOpenDiscovery(): void
    {
        $owner = User::factory()->create();
        Idea::factory()->for($owner, 'user')->create([
            'title' => 'Open collaboration',
            'summary' => 'A visible open idea.',
            'status' => Idea::STATUS_OPEN,
        ]);
        Idea::factory()->for($owner, 'user')->create([
            'title' => 'Shipped collaboration',
            'summary' => 'A hidden shipped idea.',
            'status' => Idea::STATUS_SHIPPED,
        ]);

        $this
            ->get(route('ideas.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Ideas/Index')
                ->has('ideas.items', 1)
                ->where('ideas.items.0.title', 'Open collaboration'));
    }

    public static function ideaStatuses(): array
    {
        return [
            'open' => [Idea::STATUS_OPEN, 'Open'],
            'paused' => [Idea::STATUS_PAUSED, 'Paused'],
            'shipped' => [Idea::STATUS_SHIPPED, 'Shipped'],
            'closed' => [Idea::STATUS_CLOSED, 'Closed'],
        ];
    }

    public static function inactiveIdeaStatuses(): array
    {
        return [
            'paused' => [Idea::STATUS_PAUSED],
            'shipped' => [Idea::STATUS_SHIPPED],
            'closed' => [Idea::STATUS_CLOSED],
        ];
    }
}
