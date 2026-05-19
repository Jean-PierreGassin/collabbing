<?php

namespace Tests\Feature\Ideas;

use App\Models\CodeRepository;
use App\Models\Idea;
use App\Models\RepositoryEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class RepositoryActivityArchiveTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function testRepositoryActivityArchiveListsEventsNewestFirst(): void
    {
        $owner = User::factory()->create();
        $idea = Idea::factory()
            ->for($owner)
            ->withCodeRepository('activity-repo', ['status' => CodeRepository::STATUS_ACTIVE])
            ->create();
        $repository = $this->repositoryFor($idea);

        RepositoryEvent::factory()
            ->for($repository, 'codeRepository')
            ->create([
                'summary' => 'Older repository event.',
                'occurred_at' => now()->subDay(),
                'payload' => ['private' => 'not exposed'],
            ]);
        RepositoryEvent::factory()
            ->for($repository, 'codeRepository')
            ->create([
                'summary' => 'Newest repository event.',
                'occurred_at' => now(),
                'payload' => ['private' => 'not exposed'],
            ]);

        $this
            ->get(route('ideas.repository-activity', $idea))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Ideas/RepositoryActivity')
                ->where('idea.routes.repositoryActivity', route('ideas.repository-activity', $idea))
                ->has('events.items', 2)
                ->where('events.items.0.summary', 'Newest repository event.')
                ->where('events.items.1.summary', 'Older repository event.')
                ->missing('events.items.0.payload'));
    }

    public function testRepositoryActivityArchiveHandlesIdeasWithoutRepositories(): void
    {
        $owner = User::factory()->create();
        $idea = Idea::factory()
            ->for($owner)
            ->create();

        $this
            ->get(route('ideas.repository-activity', $idea))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Ideas/RepositoryActivity')
                ->has('events.items', 0));
    }

    private function repositoryFor(Idea $idea): CodeRepository
    {
        $repository = $idea->latestCodeRepository();

        $this->assertNotNull($repository);

        return $repository;
    }
}
