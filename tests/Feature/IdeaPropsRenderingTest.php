<?php

namespace Tests\Feature;

use App\Models\Idea;
use App\Models\User;
use App\Services\Inertia\PagePropsService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class IdeaPropsRenderingTest extends TestCase
{
    use LazilyRefreshDatabase;

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

    public function test_public_user_props_do_not_expose_email_addresses(): void
    {
        $user = User::factory()->create([
            'email' => 'private@example.com',
        ]);

        $props = app(PagePropsService::class)->user($user);

        $this->assertNull($props['email']);
        $this->assertFalse($props['canUpdate']);
        $this->assertStringNotContainsString(md5('private@example.com'), $props['profilePicture']);
    }

    public function test_user_props_include_email_for_their_own_profile(): void
    {
        $user = User::factory()->create([
            'email' => 'owner@example.com',
        ]);

        $this->actingAs($user);

        $props = app(PagePropsService::class)->user($user);

        $this->assertSame('owner@example.com', $props['email']);
        $this->assertTrue($props['canUpdate']);
    }

    public function test_user_props_include_connected_github_account_state(): void
    {
        $user = User::factory()->withGithubAccount('github-token', 'octocat')->create();

        $props = app(PagePropsService::class)->user($user);

        $this->assertTrue($props['hasGithubToken']);
        $this->assertSame('octocat', $props['githubUsername']);
        $this->assertSame('https://github.com/octocat.png?size=200', $props['profilePicture']);
    }

    private function makeIdeaWithRelations(string $content): Idea
    {
        $user = new User([
            'username' => 'tester',
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'tester@example.com',
        ]);
        $user->id = 1;
        $user->created_at = Carbon::now();

        $idea = new Idea([
            'title' => 'Test title',
            'communication' => 'Slack',
            'content' => $content,
            'status' => 'open',
        ]);
        $idea->id = 1;
        $idea->created_at = Carbon::now();
        $idea->setRelation('user', $user);
        $idea->setRelation('codeRepository', null);
        $idea->setRelation('supporters', new EloquentCollection);
        $idea->setRelation('approvedApplications', new EloquentCollection);

        return $idea;
    }
}
