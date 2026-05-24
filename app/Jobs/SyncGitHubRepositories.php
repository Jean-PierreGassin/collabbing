<?php

namespace App\Jobs;

use App\Models\CodeRepository;
use App\Repositories\CodeRepositories\CodeRepositoryRepository;
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

    public function handle(IdeaRepositorySyncService $sync, CodeRepositoryRepository $repositories): void
    {
        $repositories
            ->dueForGitHubSync((int) config('services.github.repository_sync.max_per_run', 25))
            ->each(function (CodeRepository $codeRepository) use ($sync): void {
                try {
                    $sync->sync($codeRepository);
                } catch (Throwable $exception) {
                    $sync->scheduleRetry($codeRepository);

                    Log::warning('GitHub repository sync failed for idea.', [
                        'idea_id' => $codeRepository->idea_id,
                        'code_repository_id' => $codeRepository->id,
                        'repository_name' => $codeRepository->name,
                        'exception' => $exception::class,
                        'message' => $exception->getMessage(),
                    ]);
                }
            });
    }
}
