<?php

namespace Tests\Feature;

use App\Models\Idea;
use App\Models\User;
use App\Services\Inertia\PagePropsService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class IdeaPropsRenderingTest extends TestCase
{
    public function test_idea_props_render_markdown_content(): void
    {
        $idea = $this->makeIdeaWithRelations('# Test title');

        $props = app(PagePropsService::class)->idea($idea);

        $this->assertSame("<h1>Test title</h1>\n", $props['contentHtml']);
    }

    public function test_search_result_paginator_maps_rendered_markdown_content(): void
    {
        $idea = $this->makeIdeaWithRelations('# Searchable idea');
        $paginator = new LengthAwarePaginator(new EloquentCollection([$idea]), 1, 10);

        $props = app(PagePropsService::class)->paginator(
            $paginator,
            fn (Idea $idea) => app(PagePropsService::class)->idea($idea)
        );

        $this->assertSame("<h1>Searchable idea</h1>\n", $props['items'][0]['contentHtml']);
    }

    private function makeIdeaWithRelations(string $content): Idea
    {
        $user = new User([
            'username' => 'tester',
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'tester@example.com',
            'github_token' => null,
        ]);
        $user->id = 1;
        $user->created_at = Carbon::now();

        $idea = new Idea([
            'title' => 'Test title',
            'communication' => 'Slack',
            'content' => $content,
            'status' => 'open',
            'repository' => false,
            'repository_name' => 'test-title',
        ]);
        $idea->id = 1;
        $idea->created_at = Carbon::now();
        $idea->setRelation('user', $user);
        $idea->setRelation('supporters', new EloquentCollection);
        $idea->setRelation('approvedApplications', new EloquentCollection);

        return $idea;
    }
}
