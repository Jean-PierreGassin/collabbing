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
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\RateLimiter;
use Inertia\ExceptionResponse;
use Inertia\Inertia;

/**
 * Class AppServiceProvider
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
     */
    public function boot(): void
    {
        $this->registerPolicies();
        Paginator::useBootstrap();

        RateLimiter::for('product-write', function (Request $request) {
            return Limit::perMinute(20)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('integration-write', function (Request $request) {
            return Limit::perMinute(5)->by($request->user()?->id ?: $request->ip());
        });

        Inertia::handleExceptionsUsing(function (ExceptionResponse $response) {
            if (in_array($response->statusCode(), [401, 403, 404, 429, 500, 503], true)) {
                return $response
                    ->render('Error', ['status' => $response->statusCode()])
                    ->withSharedData();
            }

            return null;
        });
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->extend(
            'command.model.make',
            fn ($command, $app) => new ModelMakeCommand($app['files'])
        );
    }
}
