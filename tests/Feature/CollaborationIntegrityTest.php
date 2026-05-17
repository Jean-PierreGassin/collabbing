<?php

namespace Tests\Feature;

use App\Models\Idea;
use App\Models\IdeaApplication;
use App\Models\IdeaSupporter;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
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
}
