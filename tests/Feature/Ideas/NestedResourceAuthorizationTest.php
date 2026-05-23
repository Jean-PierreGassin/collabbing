<?php

namespace Tests\Feature\Ideas;

use App\Models\Idea;
use App\Models\IdeaApplication;
use App\Models\IdeaComment;
use App\Models\IdeaSupporter;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class NestedResourceAuthorizationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function testIdeaOwnerCannotApproveAnApplicationFromAnotherIdea(): void
    {
        $owner = User::factory()->create();
        $ownedIdea = Idea::factory()->for($owner, 'user')->create();
        $otherIdea = Idea::factory()->for(User::factory(), 'user')->create();
        $application = IdeaApplication::factory()
            ->for($otherIdea, 'idea')
            ->for(User::factory(), 'user')
            ->create([
                'status' => 'pending',
            ]);

        $response = $this
            ->actingAs($owner)
            ->put(route('ideas.applications.approve', [$ownedIdea, $application]));

        $response->assertNotFound();
        $this->assertDatabaseHas('idea_applications', [
            'id' => $application->id,
            'status' => 'pending',
        ]);
    }

    public function testIdeaOwnerCannotDeleteAnApplicationFromAnotherIdea(): void
    {
        $owner = User::factory()->create();
        $ownedIdea = Idea::factory()->for($owner, 'user')->create();
        $otherIdea = Idea::factory()->for(User::factory(), 'user')->create();
        $application = IdeaApplication::factory()
            ->for($otherIdea, 'idea')
            ->for(User::factory(), 'user')
            ->create();

        $response = $this
            ->actingAs($owner)
            ->delete(route('ideas.applications.destroy', [$ownedIdea, $application]));

        $response->assertNotFound();
        $this->assertModelExists($application);
    }

    public function testCommentOwnerCannotEditACommentThroughAnotherIdea(): void
    {
        $commenter = User::factory()->create();
        $otherIdea = Idea::factory()->for(User::factory(), 'user')->create();
        $comment = IdeaComment::factory()
            ->for($otherIdea, 'idea')
            ->for($commenter, 'user')
            ->create();
        $ownedIdea = Idea::factory()->for($commenter, 'user')->create();

        $response = $this
            ->actingAs($commenter)
            ->get(route('ideas.comments.edit', [$ownedIdea, $comment]));

        $response->assertNotFound();
    }

    public function testCommentOwnerCannotUpdateACommentThroughAnotherIdea(): void
    {
        $commenter = User::factory()->create();
        $otherIdea = Idea::factory()->for(User::factory(), 'user')->create();
        $comment = IdeaComment::factory()
            ->for($otherIdea, 'idea')
            ->for($commenter, 'user')
            ->create([
                'content' => 'Original comment',
            ]);
        $ownedIdea = Idea::factory()->for($commenter, 'user')->create();

        $response = $this
            ->actingAs($commenter)
            ->put(route('ideas.comments.update', [$ownedIdea, $comment]), [
                'content' => 'Tampered comment',
            ]);

        $response->assertNotFound();
        $this->assertDatabaseHas('idea_comments', [
            'id' => $comment->id,
            'content' => 'Original comment',
        ]);
    }

    public function testSupporterCannotDeleteSupportThroughAnotherIdea(): void
    {
        $supporterUser = User::factory()->create();
        $otherIdea = Idea::factory()->for(User::factory(), 'user')->create();
        $supporter = IdeaSupporter::factory()
            ->for($otherIdea, 'idea')
            ->for($supporterUser, 'user')
            ->create();
        $ownedIdea = Idea::factory()->for($supporterUser, 'user')->create();

        $response = $this
            ->actingAs($supporterUser)
            ->delete(route('ideas.supporters.destroy', [$ownedIdea, $supporter]));

        $response->assertNotFound();
        $this->assertModelExists($supporter);
    }

    public function testNonOwnerCannotViewAnIdeaManagementDashboard(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $idea = Idea::factory()->for($owner, 'user')->create();
        IdeaApplication::factory()
            ->for($idea, 'idea')
            ->for(User::factory(), 'user')
            ->create([
                'content' => 'Private application details',
                'status' => 'pending',
            ]);

        $response = $this
            ->actingAs($otherUser)
            ->get(route('ideas.dashboard', $idea));

        $response->assertForbidden();
        $response->assertDontSee('Private application details');
    }
}
