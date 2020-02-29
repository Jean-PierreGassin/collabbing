<?php

namespace App\Providers;

use App\Idea;
use App\IdeaApplication;
use App\IdeaComment;
use App\IdeaSupporter;
use App\Policies\IdeaApplicationPolicy;
use App\Policies\IdeaCommentPolicy;
use App\Policies\IdeaPolicy;
use App\Policies\IdeaSupporterPolicy;
use App\Policies\UserPolicy;
use App\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

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
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        //
    }
}
