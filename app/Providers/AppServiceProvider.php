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

class AppServiceProvider extends ServiceProvider
{
    protected $policies = [
        User::class => UserPolicy::class,
        Idea::class => IdeaPolicy::class,
        IdeaComment::class => IdeaCommentPolicy::class,
        IdeaSupporter::class => IdeaSupporterPolicy::class,
        IdeaApplication::class => IdeaApplicationPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
        Paginator::useBootstrap();

        RateLimiter::for('product-write', function (Request $request) {
            return Limit::perMinute(20)->by($this->rateLimitKey($request));
        });

        RateLimiter::for('integration-write', function (Request $request) {
            return Limit::perMinute(5)->by($this->rateLimitKey($request));
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

    public function register(): void
    {
        $this->app->extend(
            'command.model.make',
            fn ($command, $app) => new ModelMakeCommand($app['files'])
        );
    }

    private function rateLimitKey(Request $request): int|string|null
    {
        $user = $request->user();

        if ($user) {
            return $user->id;
        }

        return $request->ip();
    }
}
