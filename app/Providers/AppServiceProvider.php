<?php

namespace App\Providers;

use App\User;
use App\Idea;
use App\IdeaSupporter;
use App\IdeaApplication;
use App\Policies\UserPolicy;
use App\Policies\IdeaPolicy;
use App\Policies\IdeaSupporterPolicy;
use App\Policies\IdeaApplicationPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

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
        IdeaSupporter::class => IdeaSupporterPolicy::class,
        IdeaApplication::class => IdeaApplicationPolicy::class,
    ];

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
