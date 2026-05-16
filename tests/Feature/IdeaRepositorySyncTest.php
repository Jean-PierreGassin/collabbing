<?php

namespace Tests\Feature;

use App\Models\Idea;
use App\Models\User;
use App\Services\Ideas\IdeaRepositorySyncService;
use App\Services\ThirdParty\GitHub\GitHubRepositoryClient;
use Github\Exception\RuntimeException as GitHubRuntimeException;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Mockery;
use Tests\TestCase;

class IdeaRepositorySyncTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_repository_is_marked_missing_when_git_hub_returns_not_found(): void
    {
        $user = User::factory()->create([
            'github_token' => 'github-token',
            'github_username' => 'octocat',
        ]);
        $idea = Idea::factory()->for($user)->create([
            'repository' => true,
            'repository_name' => 'missing-repo',
        ]);

        $github = Mockery::mock(GitHubRepositoryClient::class);
        $github->shouldReceive('show')
            ->once()
            ->with(Mockery::on(fn (User $owner) => $owner->is($user)), 'missing-repo')
            ->andThrow(new GitHubRuntimeException('Not Found', 404));

        (new IdeaRepositorySyncService($github))->sync($idea);

        $idea->refresh();

        $this->assertFalse($idea->repository);
        $this->assertNotNull($idea->repository_missing_at);
        $this->assertNotNull($idea->repository_synced_at);
        $this->assertTrue($idea->repositoryEvents()->where('type', 'repository_missing')->exists());
    }

    public function test_repository_snapshot_stores_latest_github_activity(): void
    {
        $user = User::factory()->create([
            'github_token' => 'github-token',
            'github_username' => 'octocat',
        ]);
        $idea = Idea::factory()->for($user)->create([
            'repository' => true,
            'repository_name' => 'synced-repo',
        ]);

        $github = Mockery::mock(GitHubRepositoryClient::class);
        $github->shouldReceive('show')
            ->twice()
            ->with(Mockery::on(fn (User $owner) => $owner->is($user)), 'synced-repo')
            ->andReturn($this->repositoryPayload());
        $github->shouldReceive('latestCommit')
            ->twice()
            ->with(Mockery::on(fn (User $owner) => $owner->is($user)), 'synced-repo', 'main')
            ->andReturn($this->commitPayload());

        $sync = new IdeaRepositorySyncService($github);

        $sync->sync($idea);
        $sync->sync($idea->refresh());

        $idea->refresh();

        $this->assertTrue($idea->repository);
        $this->assertSame('https://github.com/octocat/synced-repo', $idea->repository_html_url);
        $this->assertSame('main', $idea->repository_default_branch);
        $this->assertSame(3, $idea->repository_open_issues_count);
        $this->assertSame(8, $idea->repository_stargazers_count);
        $this->assertSame('abc1234567890', $idea->repository_latest_commit_sha);
        $this->assertSame('Ship repository sync', $idea->repository_latest_commit_message);
        $this->assertSame('octocat', $idea->repository_latest_commit_author);
        $this->assertNotNull($idea->repository_pushed_at);
        $this->assertSame(1, $idea->repositoryEvents()->where('type', 'repository_commit')->count());
        $this->assertSame(1, $idea->repositoryEvents()->where('type', 'repository_synced')->count());
    }

    private function repositoryPayload(): array
    {
        return [
            'id' => 123,
            'name' => 'synced-repo',
            'full_name' => 'octocat/synced-repo',
            'html_url' => 'https://github.com/octocat/synced-repo',
            'default_branch' => 'main',
            'open_issues_count' => 3,
            'stargazers_count' => 8,
            'forks_count' => 2,
            'pushed_at' => '2026-05-16T08:00:00Z',
        ];
    }

    private function commitPayload(): array
    {
        return [
            'sha' => 'abc1234567890',
            'commit' => [
                'message' => "Ship repository sync\n\nWith details",
                'author' => [
                    'name' => 'Mona Lisa',
                ],
                'committer' => [
                    'date' => '2026-05-16T08:05:00Z',
                ],
            ],
            'author' => [
                'login' => 'octocat',
            ],
        ];
    }
}
