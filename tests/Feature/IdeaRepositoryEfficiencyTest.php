<?php

namespace Tests\Feature;

use App\Models\Idea;
use App\Models\IdeaApplication;
use App\Models\IdeaSupporter;
use App\Models\User;
use App\Repositories\Ideas\IdeaRepository;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class IdeaRepositoryEfficiencyTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_open_recent_ideas_are_loaded_with_index_relationships(): void
    {
        $idea = $this->createIdeaWithIndexRelations();

        $loadedIdea = app(IdeaRepository::class)
            ->getOpenRecent()
            ->getCollection()
            ->firstWhere('id', $idea->id);

        $this->assertIndexRelationshipsAreLoaded($loadedIdea);
    }

    public function test_search_results_are_loaded_with_index_relationships(): void
    {
        $idea = $this->createIdeaWithIndexRelations([
            'title' => 'Searchable collaboration',
        ]);

        $loadedIdea = app(IdeaRepository::class)
            ->search('Searchable')
            ->getCollection()
            ->firstWhere('id', $idea->id);

        $this->assertIndexRelationshipsAreLoaded($loadedIdea);
    }

    public function test_trending_ideas_are_loaded_with_index_relationships(): void
    {
        $idea = $this->createIdeaWithIndexRelations();

        $loadedIdea = app(IdeaRepository::class)
            ->getTrending()
            ->firstWhere('id', $idea->id);

        $this->assertIndexRelationshipsAreLoaded($loadedIdea);
    }

    public function test_dashboard_ideas_are_loaded_with_index_relationships(): void
    {
        $owner = User::factory()->create();
        $idea = $this->createIdeaWithIndexRelations([], $owner);

        $loadedIdea = app(IdeaRepository::class)
            ->getUserIdeas($owner)
            ->getCollection()
            ->firstWhere('id', $idea->id);

        $this->assertIndexRelationshipsAreLoaded($loadedIdea);
    }

    public function test_collaborated_ideas_are_loaded_with_index_relationships(): void
    {
        $collaborator = User::factory()->create();
        $idea = $this->createIdeaWithIndexRelations();

        IdeaApplication::factory()
            ->for($idea, 'idea')
            ->for($collaborator, 'user')
            ->create([
                'status' => 'approved',
            ]);

        $loadedIdea = app(IdeaRepository::class)
            ->getCollaboratedIdeas($collaborator)
            ->getCollection()
            ->firstWhere('id', $idea->id);

        $this->assertIndexRelationshipsAreLoaded($loadedIdea);
    }

    private function createIdeaWithIndexRelations(array $overrides = [], ?User $owner = null): Idea
    {
        $idea = Idea::factory()
            ->for($owner ?? User::factory(), 'user')
            ->create(array_merge([
                'status' => 'open',
            ], $overrides));

        IdeaSupporter::factory()
            ->for($idea, 'idea')
            ->for(User::factory(), 'user')
            ->create();

        IdeaApplication::factory()
            ->for($idea, 'idea')
            ->for(User::factory(), 'user')
            ->create([
                'status' => 'approved',
            ]);

        return $idea;
    }

    private function assertIndexRelationshipsAreLoaded(?Idea $idea): void
    {
        $this->assertInstanceOf(Idea::class, $idea);
        $this->assertTrue($idea->relationLoaded('user'));
        $this->assertTrue($idea->relationLoaded('supporters'));
        $this->assertTrue($idea->relationLoaded('approvedApplications'));
        $this->assertTrue($idea->approvedApplications->first()->relationLoaded('user'));
    }
}
