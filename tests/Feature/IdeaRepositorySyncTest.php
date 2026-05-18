<?php

namespace Tests\Feature;

use App\Jobs\SyncGitHubRepositories;
use App\Models\CodeRepository;
use App\Models\Idea;
use App\Models\User;
use App\Services\Ideas\IdeaRepositorySyncService;
use App\Services\ThirdParty\GitHub\GitHubRepositoryClient;
use Carbon\Carbon;
use Github\Exception\RuntimeException as GitHubRuntimeException;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\MockObject\MockObject;
use RuntimeException;
use Tests\TestCase;

class IdeaRepositorySyncTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function testRepositoryIsMarkedMissingWhenGitHubReturnsNotFound(): void
    {
        Carbon::setTestNow('2026-05-16 10:00:00');

        $user = User::factory()->withGithubAccount('github-token', 'octocat')->create();
        $idea = Idea::factory()
            ->for($user)
            ->withCodeRepository('missing-repo', ['status' => CodeRepository::STATUS_ACTIVE])
            ->create();
        $codeRepository = $this->repositoryFor($idea);

        $github = $this->createMock(GitHubRepositoryClient::class);
        $github->expects($this->once())
            ->method('show')
            ->with($this->callback(fn (User $owner) => $owner->is($user)), 'missing-repo')
            ->willThrowException(new GitHubRuntimeException('Not Found', 404));

        (new IdeaRepositorySyncService($github))->sync($codeRepository);

        $codeRepository->refresh();

        $this->assertSame(CodeRepository::STATUS_MISSING, $codeRepository->status);
        $this->assertNotNull($codeRepository->missing_at);
        $this->assertNotNull($codeRepository->synced_at);
        $this->assertNull($codeRepository->sync_due_at);
        $this->assertTrue($codeRepository->events()->where('type', 'repository_missing')->exists());
    }

    public function testRepositorySnapshotStoresLatestGithubActivity(): void
    {
        Carbon::setTestNow('2026-05-16 10:00:00');

        $user = User::factory()->withGithubAccount('github-token', 'octocat')->create();
        $idea = Idea::factory()
            ->for($user)
            ->withCodeRepository('synced-repo', ['status' => CodeRepository::STATUS_ACTIVE])
            ->create();
        $codeRepository = $this->repositoryFor($idea);

        $github = $this->createMock(GitHubRepositoryClient::class);
        $github->expects($this->exactly(2))
            ->method('show')
            ->with($this->callback(fn (User $owner) => $owner->is($user)), 'synced-repo')
            ->willReturn($this->repositoryPayload());
        $github->expects($this->exactly(2))
            ->method('latestCommit')
            ->with($this->callback(fn (User $owner) => $owner->is($user)), 'synced-repo', 'main')
            ->willReturn($this->commitPayload());

        $sync = new IdeaRepositorySyncService($github);

        $sync->sync($codeRepository);
        $codeRepository->refresh();
        $sync->sync($codeRepository);

        $codeRepository->refresh();

        $this->assertSame(CodeRepository::STATUS_ACTIVE, $codeRepository->status);
        $this->assertSame('https://github.com/octocat/synced-repo', $codeRepository->html_url);
        $this->assertSame('main', $codeRepository->default_branch);
        $this->assertSame(3, $codeRepository->open_issues_count);
        $this->assertSame(8, $codeRepository->stargazers_count);
        $this->assertSame('abc1234567890', $codeRepository->latest_commit_sha);
        $this->assertSame('Ship repository sync', $codeRepository->latest_commit_message);
        $this->assertSame('octocat', $codeRepository->latest_commit_author);
        $this->assertNotNull($codeRepository->pushed_at);
        $this->assertNotNull($codeRepository->sync_due_at);
        $this->assertTrue($this->syncDueAt($codeRepository)->betweenIncluded(
            now()->addMinutes(360),
            now()->addMinutes(720)
        ));
        $this->assertSame(1, $codeRepository->events()->where('type', 'repository_commit')->count());
        $this->assertSame(1, $codeRepository->events()->where('type', 'repository_synced')->count());
    }

    public function testRepositorySyncJobOnlyProcessesDueRepositoriesInASmallBatch(): void
    {
        Carbon::setTestNow('2026-05-16 10:00:00');
        config(['services.github.repository_sync.max_per_run' => 2]);

        $user = User::factory()->withGithubAccount('github-token', 'octocat')->create();

        $dueRepositories = collect([
            ['sync_due_at' => now()->subMinutes(10)],
            ['sync_due_at' => now()->subMinute()],
            ['sync_due_at' => null],
        ])->map(fn (array $attributes) => CodeRepository::factory()
            ->for(Idea::factory()->for($user), 'idea')
            ->create(array_merge([
                'provider' => CodeRepository::PROVIDER_GITHUB,
                'status' => CodeRepository::STATUS_ACTIVE,
            ], $attributes)));

        CodeRepository::factory()
            ->for(Idea::factory()->for($user), 'idea')
            ->create([
                'provider' => CodeRepository::PROVIDER_GITHUB,
                'status' => CodeRepository::STATUS_ACTIVE,
                'sync_due_at' => now()->addHour(),
            ]);

        $sync = $this->syncService();
        $sync->expects($this->exactly(2))
            ->method('sync')
            ->with($this->callback(fn (CodeRepository $codeRepository) => $dueRepositories->contains(fn (CodeRepository $dueRepository) => $dueRepository->is($codeRepository))));

        (new SyncGitHubRepositories)->handle($sync);
    }

    private function repositoryFor(Idea $idea): CodeRepository
    {
        $codeRepository = $idea->latestCodeRepository();

        if (! $codeRepository) {
            throw new RuntimeException('The test idea should have a repository.');
        }

        return $codeRepository;
    }

    private function syncDueAt(CodeRepository $codeRepository): Carbon
    {
        $syncDueAt = $codeRepository->getAttribute('sync_due_at');

        if ($syncDueAt instanceof Carbon) {
            return $syncDueAt;
        }

        if (is_string($syncDueAt)) {
            return Carbon::parse($syncDueAt);
        }

        throw new RuntimeException('The repository should have a sync due date.');
    }

    private function syncService(): IdeaRepositorySyncService&MockObject
    {
        return $this->createMock(IdeaRepositorySyncService::class);
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
