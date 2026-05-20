<?php

namespace Tests\Feature\Ideas;

use App\Models\Idea;
use App\Models\IdeaApplication;
use App\Models\IdeaComment;
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

    public function testIdeaPropsRenderMarkdownContent(): void
    {
        $idea = $this->makeIdeaWithRelations('# Test title');

        $props = app(PagePropsService::class)->idea($idea);

        $this->assertSame('Plain summary for the idea card.', $props['summary']);
        $this->assertSame("<h1 id=\"test-title\">Test title</h1>\n", $props['contentHtml']);
    }

    public function testIdeaPropsFallBackToPlainTextSummaryForExistingIdeas(): void
    {
        $idea = $this->makeIdeaWithRelations('# Existing title');
        $idea->summary = null;

        $props = app(PagePropsService::class)->idea($idea);

        $this->assertSame('Existing title', $props['summary']);
    }

    public function testIdeaCardTaglineDoesNotFallBackToSummary(): void
    {
        $idea = $this->makeIdeaWithRelations('# Existing title');
        $idea->tagline = null;

        $props = app(PagePropsService::class)->idea($idea);

        $this->assertSame('Open for collaborators around Test title.', $props['tagline']);
    }

    public function testIdeaCardTaglineIsCappedForCards(): void
    {
        $idea = $this->makeIdeaWithRelations('# Existing title');
        $idea->tagline = str_repeat('a', 61);

        $props = app(PagePropsService::class)->idea($idea);

        $this->assertSame(str_repeat('a', 60), $props['tagline']);
    }

    public function testSearchResultPaginatorMapsRenderedMarkdownContent(): void
    {
        $idea = $this->makeIdeaWithRelations('# Searchable idea');
        $paginator = new LengthAwarePaginator(new EloquentCollection([$idea]), 1, 10);

        $props = app(PagePropsService::class)->paginator(
            $paginator,
            fn (Idea $idea) => app(PagePropsService::class)->idea($idea)
        );

        $this->assertSame("<h1 id=\"searchable-idea\">Searchable idea</h1>\n", $props['items'][0]['contentHtml']);
    }

    public function testCommentPropsRenderMarkdownContent(): void
    {
        $user = $this->makeUser();
        $comment = new IdeaComment([
            'content' => '**Useful** comment',
        ]);
        $comment->id = 1;
        $comment->idea_id = 1;
        $comment->created_at = Carbon::now();
        $comment->updated_at = Carbon::now();
        $comment->setRelation('user', $user);
        $comment->setRelation('replies', new EloquentCollection);

        $props = app(PagePropsService::class)->comment($comment);

        $this->assertSame("<p><strong>Useful</strong> comment</p>\n", $props['contentHtml']);
    }

    public function testCommentPropsRenderProfileLinksForMentions(): void
    {
        $commenter = $this->makeUser();
        User::factory()->create([
            'username' => 'target_user',
        ]);
        $comment = new IdeaComment([
            'content' => 'Can @target_user review this?',
        ]);
        $comment->id = 1;
        $comment->idea_id = 1;
        $comment->created_at = Carbon::now();
        $comment->updated_at = Carbon::now();
        $comment->setRelation('user', $commenter);
        $comment->setRelation('replies', new EloquentCollection);

        $props = app(PagePropsService::class)->comment($comment);

        $this->assertStringContainsString('/users/target_user', $props['contentHtml']);
        $this->assertStringContainsString('@target_user</a>', $props['contentHtml']);
    }

    public function testApplicationPropsRenderMarkdownContent(): void
    {
        $user = $this->makeUser();
        $application = new IdeaApplication([
            'content' => '- Can build APIs',
            'status' => 'pending',
        ]);
        $application->id = 1;
        $application->idea_id = 1;
        $application->created_at = Carbon::now();
        $application->setRelation('user', $user);

        $props = app(PagePropsService::class)->application($application);

        $this->assertSame("<ul>\n<li>Can build APIs</li>\n</ul>\n", $props['contentHtml']);
    }

    public function testPublicUserPropsDoNotExposeEmailAddresses(): void
    {
        $user = User::factory()->create([
            'email' => 'private@example.com',
        ]);

        $props = app(PagePropsService::class)->user($user);

        $this->assertNull($props['email']);
        $this->assertFalse($props['canUpdate']);
        $this->assertStringNotContainsString(md5('private@example.com'), $props['profilePicture']);
    }

    public function testUserPropsIncludeEmailForTheirOwnProfile(): void
    {
        $user = User::factory()->create([
            'email' => 'owner@example.com',
        ]);

        $this->actingAs($user);

        $props = app(PagePropsService::class)->user($user);

        $this->assertSame('owner@example.com', $props['email']);
        $this->assertTrue($props['canUpdate']);
    }

    public function testUserPropsIncludeConnectedGithubAccountState(): void
    {
        $user = User::factory()->withGithubAccount('github-token', 'octocat')->create();

        $props = app(PagePropsService::class)->user($user);

        $this->assertTrue($props['hasGithubToken']);
        $this->assertSame('octocat', $props['githubUsername']);
        $this->assertSame('https://github.com/octocat.png?size=200', $props['profilePicture']);
    }

    private function makeIdeaWithRelations(string $content): Idea
    {
        $user = $this->makeUser();

        $idea = new Idea([
            'title' => 'Test title',
            'tagline' => 'Plain tagline for the idea card.',
            'summary' => 'Plain summary for the idea card.',
            'tags' => ['testing', 'markdown'],
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

    private function makeUser(): User
    {
        $user = new User([
            'username' => 'tester',
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'tester@example.com',
        ]);
        $user->id = 1;
        $user->created_at = Carbon::now();

        return $user;
    }
}
