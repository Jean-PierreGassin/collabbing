<?php

namespace Tests\Feature;

use App\Models\Idea;
use App\Models\User;
use App\Services\Inertia\PagePropsService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
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
            'github_username' => null,
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

    public function test_user_props_do_not_decrypt_legacy_plaintext_github_tokens_for_presence_checks(): void
    {
        $userId = DB::table('users')->insertGetId([
            'username' => 'legacy-token-user',
            'first_name' => 'Legacy',
            'last_name' => 'Token',
            'email' => 'legacy-token@example.com',
            'password' => bcrypt('password'),
            'github_token' => 'legacy-plaintext-token',
            'github_username' => 'octocat',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $user = User::findOrFail($userId);

        $props = app(PagePropsService::class)->user($user);

        $this->assertTrue($props['hasGithubToken']);
        $this->assertSame('https://github.com/octocat.png?size=200', $props['profilePicture']);
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
