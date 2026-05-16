<?php

namespace App\Jobs;

use App\Models\Idea;
use App\Services\Ideas\IdeaRepositorySyncService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class SyncGitHubRepositories implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 120;

    public int $uniqueFor = 300;

    public array $backoff = [60, 300, 900];

    public function uniqueId(): string
    {
        return 'github-repository-sync';
    }

    public function handle(IdeaRepositorySyncService $sync): void
    {
        Idea::query()
            ->where('repository', true)
            ->where(fn ($query) => $query
                ->whereNull('repository_sync_due_at')
                ->orWhere('repository_sync_due_at', '<=', now()))
            ->whereHas('user', fn ($query) => $query
                ->whereNotNull('github_token')
                ->whereNotNull('github_username'))
            ->with('user')
            ->oldest('repository_sync_due_at')
            ->limit((int) config('services.github.repository_sync.max_per_run', 25))
            ->get()
            ->each(function (Idea $idea) use ($sync): void {
                try {
                    $sync->sync($idea);
                } catch (Throwable $exception) {
                    $sync->scheduleRetry($idea);

                    Log::warning('GitHub repository sync failed for idea.', [
                        'idea_id' => $idea->id,
                        'repository_name' => $idea->repository_name,
                        'exception' => $exception::class,
                        'message' => $exception->getMessage(),
                    ]);
                }
            });
    }
}
