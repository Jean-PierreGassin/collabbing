<?php

namespace App\Console;

use App\Jobs\SyncGitHubRepositories;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        //
    ];

    protected function schedule(Schedule $schedule): void
    {
        $schedule->job(new SyncGitHubRepositories)
            ->name('github-repository-sync')
            ->everyFiveMinutes()
            ->withoutOverlapping(10)
            ->onOneServer();
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
