<?php

namespace Tests\Feature;

use App\Models\Idea;
use App\Models\IdeaApplication;
use App\Models\IdeaComment;
use App\Models\IdeaSupporter;
use App\Models\User;
use App\Services\Ideas\IdeaService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CollaborationIntegrityTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_supporting_an_idea_is_idempotent(): void
    {
        $owner = User::factory()->create();
        $supporter = User::factory()->create();
        $idea = Idea::factory()->for($owner, 'user')->create();

        $this->actingAs($supporter)
            ->post(route('ideas.supporters.store', $idea))
            ->assertRedirect();

        $this->actingAs($supporter)
            ->post(route('ideas.supporters.store', $idea))
            ->assertRedirect();

        $this->assertDatabaseCount('idea_supporters', 1);
        $this->assertDatabaseHas('idea_supporters', [
            'idea_id' => $idea->id,
            'user_id' => $supporter->id,
        ]);
    }

    public function test_supporter_uniqueness_is_enforced_by_the_database(): void
    {
        $owner = User::factory()->create();
        $supporter = User::factory()->create();
        $idea = Idea::factory()->for($owner, 'user')->create();

        IdeaSupporter::factory()
            ->for($idea, 'idea')
            ->for($supporter, 'user')
            ->create();

        $this->expectException(QueryException::class);

        IdeaSupporter::factory()
            ->for($idea, 'idea')
            ->for($supporter, 'user')
            ->create();
    }

    public function test_user_with_pending_application_cannot_apply_to_same_idea_again(): void
    {
        $owner = User::factory()->create();
        $applicant = User::factory()->create();
        $idea = Idea::factory()->for($owner, 'user')->create();
        IdeaApplication::factory()
            ->for($idea, 'idea')
            ->for($applicant, 'user')
            ->create([
                'status' => 'pending',
            ]);

        $this->actingAs($applicant)
            ->post(route('ideas.applications.store', $idea), [
                'content' => 'I would like to collaborate on this idea.',
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('idea_applications', 1);
    }

    public function test_declined_applicant_can_submit_a_new_application(): void
    {
        $owner = User::factory()->create();
        $applicant = User::factory()->create();
        $idea = Idea::factory()->for($owner, 'user')->create();
        IdeaApplication::factory()
            ->for($idea, 'idea')
            ->for($applicant, 'user')
            ->create([
                'status' => 'declined',
            ]);

        $this->actingAs($applicant)
            ->post(route('ideas.applications.store', $idea), [
                'content' => 'I have revised my proposal and can help.',
            ])
            ->assertRedirect(route('ideas.show', $idea));

        $this->assertDatabaseCount('idea_applications', 2);
        $this->assertDatabaseHas('idea_applications', [
            'idea_id' => $idea->id,
            'user_id' => $applicant->id,
            'status' => 'pending',
        ]);
    }

    public function test_idea_owner_can_reply_to_a_comment_thread(): void
    {
        $owner = User::factory()->create();
        $idea = Idea::factory()->for($owner, 'user')->create();
        $comment = IdeaComment::factory()
            ->for($idea, 'idea')
            ->for($owner, 'user')
            ->create();

        $this->actingAs($owner)
            ->post(route('ideas.comments.store', $idea), [
                'content' => 'Threaded replies keep the discussion tidy.',
                'parent_id' => $comment->id,
            ])
            ->assertRedirect(route('ideas.show', $idea));

        $this->assertDatabaseHas('idea_comments', [
            'idea_id' => $idea->id,
            'user_id' => $owner->id,
            'parent_id' => $comment->id,
            'content' => 'Threaded replies keep the discussion tidy.',
        ]);
    }

    public function test_comment_reply_parent_must_belong_to_the_same_idea(): void
    {
        $owner = User::factory()->create();
        $idea = Idea::factory()->for($owner, 'user')->create();
        $otherIdea = Idea::factory()->for($owner, 'user')->create();
        $otherComment = IdeaComment::factory()
            ->for($otherIdea, 'idea')
            ->for($owner, 'user')
            ->create();

        $this->actingAs($owner)
            ->post(route('ideas.comments.store', $idea), [
                'content' => 'This should not attach across ideas.',
                'parent_id' => $otherComment->id,
            ])
            ->assertSessionHasErrors('parent_id');

        $this->assertDatabaseMissing('idea_comments', [
            'idea_id' => $idea->id,
            'parent_id' => $otherComment->id,
            'content' => 'This should not attach across ideas.',
        ]);
    }

    public function test_root_comments_are_listed_latest_first(): void
    {
        $owner = User::factory()->create();
        $idea = Idea::factory()->for($owner, 'user')->create();
        $olderComment = IdeaComment::factory()
            ->for($idea, 'idea')
            ->for($owner, 'user')
            ->create([
                'created_at' => now()->subMinutes(5),
            ]);
        $newerComment = IdeaComment::factory()
            ->for($idea, 'idea')
            ->for($owner, 'user')
            ->create([
                'created_at' => now(),
            ]);

        $comments = app(IdeaService::class)->getComments($idea);

        $this->assertSame(
            [$newerComment->id, $olderComment->id],
            $comments->getCollection()->pluck('id')->all()
        );
    }

    public function test_root_comments_are_paginated_into_small_page_sets(): void
    {
        $owner = User::factory()->create();
        $idea = Idea::factory()->for($owner, 'user')->create();

        IdeaComment::factory()
            ->count(6)
            ->for($idea, 'idea')
            ->for($owner, 'user')
            ->create();

        $comments = app(IdeaService::class)->getComments($idea);

        $this->assertCount(5, $comments->getCollection());
        $this->assertSame(2, $comments->lastPage());
        $this->assertStringContainsString('comments=2', $comments->url(2));
    }

    public function test_dashboard_idea_lists_are_paginated(): void
    {
        $user = User::factory()->create();
        $otherOwner = User::factory()->create();

        Idea::factory()
            ->count(6)
            ->for($user, 'user')
            ->create();

        Idea::factory()
            ->count(6)
            ->for($otherOwner, 'user')
            ->create()
            ->each(function (Idea $idea) use ($user): void {
                IdeaApplication::factory()
                    ->for($idea, 'idea')
                    ->for($user, 'user')
                    ->create([
                        'status' => 'approved',
                    ]);
            });

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')
                ->has('ideas.items', 5)
                ->where('ideas.lastPage', 2)
                ->has('collaborations.items', 5)
                ->where('collaborations.lastPage', 2));
    }

    public function test_dashboard_idea_lists_can_be_searched(): void
    {
        $user = User::factory()->create();

        $matchingIdea = Idea::factory()
            ->for($user, 'user')
            ->create([
                'title' => 'Searchable roadmap',
                'summary' => 'A dashboard match worth finding.',
            ]);

        Idea::factory()
            ->for($user, 'user')
            ->create([
                'title' => 'Hidden backlog',
                'summary' => 'Not the thing being searched.',
            ]);

        $this->actingAs($user)
            ->get(route('dashboard', ['search' => 'Searchable']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')
                ->where('keyword', 'Searchable')
                ->has('ideas.items', 1)
                ->where('ideas.items.0.id', $matchingIdea->id));
    }

    public function test_idea_management_lists_are_paginated(): void
    {
        $owner = User::factory()->create();
        $idea = Idea::factory()->for($owner, 'user')->create();

        IdeaApplication::factory()
            ->count(11)
            ->for($idea, 'idea')
            ->for(User::factory(), 'user')
            ->create([
                'status' => 'pending',
            ]);

        IdeaApplication::factory()
            ->count(11)
            ->for($idea, 'idea')
            ->for(User::factory(), 'user')
            ->create([
                'status' => 'approved',
            ]);

        $this->actingAs($owner)
            ->get(route('ideas.dashboard', $idea))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Ideas/Manage')
                ->has('applications.items', 10)
                ->where('applications.lastPage', 2)
                ->has('collaborators.items', 10)
                ->where('collaborators.lastPage', 2));
    }
}
