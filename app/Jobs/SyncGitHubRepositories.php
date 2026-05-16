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

    public int $uniqueFor = 1800;

    public array $backoff = [60, 300, 900];

    public function uniqueId(): string
    {
        return 'github-repository-sync';
    }

    public function handle(IdeaRepositorySyncService $sync): void
    {
        Idea::query()
            ->where('repository', true)
            ->whereHas('user', fn ($query) => $query
                ->whereNotNull('github_token')
                ->whereNotNull('github_username'))
            ->with('user')
            ->chunkById(50, function ($ideas) use ($sync) {
                foreach ($ideas as $idea) {
                    try {
                        $sync->sync($idea);
                    } catch (Throwable $exception) {
                        Log::warning('GitHub repository sync failed for idea.', [
                            'idea_id' => $idea->id,
                            'repository_name' => $idea->repository_name,
                            'exception' => $exception::class,
                            'message' => $exception->getMessage(),
                        ]);
                    }
                }
            });
    }
}
