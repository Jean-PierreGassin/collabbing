<?php

use App\Http\Controllers\Auth\SocialController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IdeaApplicationController;
use App\Http\Controllers\IdeaApplicationMessageController;
use App\Http\Controllers\IdeaApplicationReadStateController;
use App\Http\Controllers\IdeaCommentController;
use App\Http\Controllers\IdeaController;
use App\Http\Controllers\IdeaSupporterController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn () => Inertia::render('Home'))->name('home');

Route::get('/up', fn () => response()->noContent())->name('health');

Route::get('/app/{path?}', fn () => redirect()->route('home'))
    ->where('path', '.*')
    ->name('app');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->name('dashboard');

Route::prefix('users')->group(function (): void {
    Auth::routes();

    Route::name('users.')->group(function (): void {
        Route::get('/', [UserController::class, 'index'])
            ->name('index');

        Route::get('{user:username}', [UserController::class, 'show'])
            ->name('show');

        Route::middleware('auth')->group(function (): void {
            Route::get('{user:username}/edit', [UserController::class, 'edit'])
                ->name('edit');

            Route::put('{user:username}', [UserController::class, 'update'])
                ->middleware('throttle:product-write')
                ->name('update');
        });
    });
});

Route::prefix('ideas')->name('ideas.')->group(function (): void {
    Route::middleware('auth')->group(function (): void {
        Route::get('create', [IdeaController::class, 'create'])
            ->name('create');

        Route::post('/', [IdeaController::class, 'store'])
            ->middleware('throttle:product-write')
            ->name('store');

        Route::get('{idea}/edit', [IdeaController::class, 'edit'])
            ->name('edit');

        Route::match(['put', 'patch'], '{idea}', [IdeaController::class, 'update'])
            ->middleware('throttle:product-write')
            ->name('update');
    });

    Route::get('/', [IdeaController::class, 'index'])
        ->name('index');

    Route::get('{idea}', [IdeaController::class, 'show'])
        ->name('show');
});

Route::scopeBindings()
    ->prefix('ideas/{idea}')
    ->name('ideas.')
    ->middleware('auth')
    ->group(function (): void {
        Route::get('dashboard', [IdeaController::class, 'dashboard'])
            ->name('dashboard');

        Route::middleware('throttle:integration-write')->group(function (): void {
            Route::post('repository-create', [IdeaController::class, 'createRepository'])
                ->name('repository-create');

            Route::post('repository-invite', [IdeaController::class, 'inviteUsersToRepository'])
                ->name('repository-invite');
        });

        Route::get('comments/create', [IdeaCommentController::class, 'create'])
            ->name('comments.create');

        Route::get('comments/{comment}/edit', [IdeaCommentController::class, 'edit'])
            ->name('comments.edit');

        Route::get('applications/create', [IdeaApplicationController::class, 'create'])
            ->name('applications.create');

        Route::get('applications/{application}/edit', [IdeaApplicationController::class, 'edit'])
            ->name('applications.edit');

        Route::middleware('throttle:product-write')->group(function (): void {
            Route::post('comments', [IdeaCommentController::class, 'store'])
                ->name('comments.store');

            Route::match(['put', 'patch'], 'comments/{comment}', [IdeaCommentController::class, 'update'])
                ->name('comments.update');

            Route::post('supporters', [IdeaSupporterController::class, 'store'])
                ->name('supporters.store');

            Route::delete('supporters/{supporter}', [IdeaSupporterController::class, 'destroy'])
                ->name('supporters.destroy');

            Route::post('applications', [IdeaApplicationController::class, 'store'])
                ->name('applications.store');

            Route::match(['put', 'patch'], 'applications/{application}', [IdeaApplicationController::class, 'update'])
                ->name('applications.update');

            Route::post('applications/{application}/messages', [IdeaApplicationMessageController::class, 'store'])
                ->name('applications.messages.store');

            Route::put('applications/{application}/read-state', [IdeaApplicationReadStateController::class, 'update'])
                ->name('applications.read-state.update');

            Route::delete('applications/{application}', [IdeaApplicationController::class, 'destroy'])
                ->name('applications.destroy');

            Route::put('applications/{application}/approve', [IdeaApplicationController::class, 'approveApplication'])
                ->name('applications.approve');
        });
    });

Route::prefix('auth/github')
    ->name('auth.github.')
    ->middleware('auth')
    ->group(function (): void {
        Route::get('/', [SocialController::class, 'redirectToProvider'])
            ->name('login');

        Route::get('callback', [SocialController::class, 'handleProviderCallback'])
            ->name('callback');

        Route::delete('revoke', [SocialController::class, 'revokeProvider'])
            ->middleware('throttle:integration-write')
            ->name('revoke');
    });

Route::prefix('resources')->name('resources.')->group(function (): void {
    Route::get('feedback', fn () => Inertia::render('Feedback'))
        ->name('feedback');

    Route::get('contact', fn () => Inertia::render('Contact'))
        ->name('contact');

    Route::get('pricing', fn () => Inertia::render('Pricing'))
        ->name('pricing');
});
