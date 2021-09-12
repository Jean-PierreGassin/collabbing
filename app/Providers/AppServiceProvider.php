<?php

namespace App\Providers;

use App\Console\Commands\ModelMakeCommand;
use App\Models\Idea;
use App\Models\IdeaApplication;
use App\Models\IdeaComment;
use App\Models\IdeaSupporter;
use App\Models\User;
use App\Policies\IdeaApplicationPolicy;
use App\Policies\IdeaCommentPolicy;
use App\Policies\IdeaPolicy;
use App\Policies\IdeaSupporterPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Pagination\Paginator;

/**
 * Class AppServiceProvider
 * @package App\Providers
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        User::class => UserPolicy::class,
        Idea::class => IdeaPolicy::class,
        IdeaComment::class => IdeaCommentPolicy::class,
        IdeaSupporter::class => IdeaSupporterPolicy::class,
        IdeaApplication::class => IdeaApplicationPolicy::class,
    ];

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->registerPolicies();
        Paginator::useBootstrap();
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->extend(
            'command.model.make',
            fn($command, $app) => new ModelMakeCommand($app['files'])
        );
    }
}
