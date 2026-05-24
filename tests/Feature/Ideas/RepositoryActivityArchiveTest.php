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

    public function testArchiveShowsNewestEventsFirstWithoutPayloads(): void
    {
        $user = User::factory()->create();
        $idea = Idea::factory()
            ->for($user, 'user')
            ->withCodeRepository('collab-idea', [
                'status' => CodeRepository::STATUS_ACTIVE,
                'synced_at' => now()->subMinutes(5),
            ])
            ->create();
        $repository = $idea->fresh()->latestCodeRepository();

        $oldEvent = RepositoryEvent::factory()
            ->for($repository, 'codeRepository')
            ->create([
                'summary' => 'Old sync event',
                'occurred_at' => now()->subHours(2),
                'payload' => ['raw' => 'secret'],
            ]);
        $newEvent = RepositoryEvent::factory()
            ->for($repository, 'codeRepository')
            ->create([
                'type' => 'repository_commit',
                'summary' => 'New commit event',
                'occurred_at' => now()->subMinute(),
                'payload' => ['raw' => 'secret'],
            ]);

        $this
            ->get(route('ideas.repository-activity', $idea))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Ideas/RepositoryActivity')
                ->where('idea.id', $idea->id)
                ->where('archive.repository.name', 'collab-idea')
                ->has('archive.events.items', 2)
                ->where('archive.events.items.0.id', $newEvent->id)
                ->where('archive.events.items.0.summary', 'New commit event')
                ->missing('archive.events.items.0.payload')
                ->where('archive.events.items.1.id', $oldEvent->id)
                ->missing('archive.events.items.1.payload'));
    }

    public function testArchivePaginatesRepositoryEvents(): void
    {
        $user = User::factory()->create();
        $idea = Idea::factory()
            ->for($user, 'user')
            ->withCodeRepository('collab-idea', ['status' => CodeRepository::STATUS_ACTIVE])
            ->create();
        $repository = $idea->fresh()->latestCodeRepository();

        RepositoryEvent::factory()
            ->count(16)
            ->for($repository, 'codeRepository')
            ->sequence(fn ($sequence): array => [
                'summary' => "Event {$sequence->index}",
                'occurred_at' => now()->subMinutes($sequence->index),
            ])
            ->create();

        $this
            ->get(route('ideas.repository-activity', $idea))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Ideas/RepositoryActivity')
                ->has('archive.events.items', 15)
                ->where('archive.events.currentPage', 1)
                ->where('archive.events.lastPage', 2)
                ->where('archive.events.previousPageUrl', null)
                ->where('archive.events.nextPageUrl', fn (?string $url): bool => is_string($url) && str_contains($url, 'page=2')));
    }

    public function testArchiveHandlesIdeasWithoutRepository(): void
    {
        $user = User::factory()->create();
        $idea = Idea::factory()->for($user, 'user')->create();

        $this
            ->get(route('ideas.repository-activity', $idea))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Ideas/RepositoryActivity')
                ->where('archive', null));
    }

    public function testArchivePreservesMissingRepositoryState(): void
    {
        $user = User::factory()->create();
        $idea = Idea::factory()
            ->for($user, 'user')
            ->withCodeRepository('missing-repo', [
                'status' => CodeRepository::STATUS_MISSING,
                'missing_at' => now()->subHour(),
            ])
            ->create();

        $this
            ->get(route('ideas.repository-activity', $idea))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Ideas/RepositoryActivity')
                ->where('archive.repository.name', 'missing-repo')
                ->where('archive.repository.isMissing', true));
    }
}
