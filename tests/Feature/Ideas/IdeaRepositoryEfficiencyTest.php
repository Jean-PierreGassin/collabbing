<?php

namespace Tests\Feature\Ideas;

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

    public function testOpenRecentIdeasAreLoadedWithIndexRelationships(): void
    {
        $idea = $this->createIdeaWithIndexRelations();

        $loadedIdea = app(IdeaRepository::class)
            ->getOpenRecent()
            ->getCollection()
            ->firstWhere('id', $idea->id);

        $this->assertIndexRelationshipsAreLoaded($loadedIdea);
    }

    public function testSearchResultsAreLoadedWithIndexRelationships(): void
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

    public function testTrendingIdeasAreLoadedWithIndexRelationships(): void
    {
        $idea = $this->createIdeaWithIndexRelations();

        $loadedIdea = app(IdeaRepository::class)
            ->getTrending()
            ->firstWhere('id', $idea->id);

        $this->assertIndexRelationshipsAreLoaded($loadedIdea);
    }

    public function testTrendingIdeasRequireSupportOrCollaborationSignal(): void
    {
        $quietIdea = Idea::factory()
            ->for(User::factory(), 'user')
            ->create([
                'status' => 'open',
            ]);

        $activeIdea = $this->createIdeaWithIndexRelations();

        $trendingIds = app(IdeaRepository::class)
            ->getTrending()
            ->pluck('id');

        $recentIds = app(IdeaRepository::class)
            ->getOpenRecent()
            ->getCollection()
            ->pluck('id');

        $this->assertTrue($trendingIds->contains($activeIdea->id));
        $this->assertFalse($trendingIds->contains($quietIdea->id));
        $this->assertTrue($recentIds->contains($quietIdea->id));
    }

    public function testDashboardIdeasAreLoadedWithIndexRelationships(): void
    {
        $owner = User::factory()->create();
        $idea = $this->createIdeaWithIndexRelations([], $owner);

        $loadedIdea = app(IdeaRepository::class)
            ->getUserIdeas($owner)
            ->getCollection()
            ->firstWhere('id', $idea->id);

        $this->assertIndexRelationshipsAreLoaded($loadedIdea);
    }

    public function testCollaboratedIdeasAreLoadedWithIndexRelationships(): void
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

        $this->assertIndexRelationshipsAreLoaded($loadedIdea, 2);
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

    private function assertIndexRelationshipsAreLoaded(?Idea $idea, int $approvedApplicationsCount = 1): void
    {
        $this->assertInstanceOf(Idea::class, $idea);
        $this->assertTrue($idea->relationLoaded('user'));
        $this->assertSame(1, $idea->getAttribute('supporters_count'));
        $this->assertSame($approvedApplicationsCount, $idea->getAttribute('approved_applications_count'));
        $this->assertFalse($idea->relationLoaded('supporters'));
        $this->assertFalse($idea->relationLoaded('approvedApplications'));
    }
}
