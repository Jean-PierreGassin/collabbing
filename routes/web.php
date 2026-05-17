<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get(
    '/',
    function () {
        return Inertia\Inertia::render('Home');
    }
)->name('home');

Route::get('/up', fn () => response()->noContent())->name('health');

Route::get('/app/{path?}', function () {
    return redirect()->route('home');
})
    ->where('path', '.*')
    ->name('app');

/**
 * Resources for Users
 */
Route::get('/dashboard', 'DashboardController@index')
    ->name('dashboard');

Route::prefix('users')->group(
    function () {
        Auth::routes();

        Route::get('/', 'UserController@index')
            ->name('users.index')
            ->middleware(['web']);

        Route::get('{user:username}', 'UserController@show')
            ->name('users.show')
            ->middleware(['web']);

        Route::put('{user:username}', 'UserController@update')
            ->name('users.update')
            ->middleware(['web', 'auth', 'throttle:product-write']);

        Route::get('{user:username}/edit', 'UserController@edit')
            ->name('users.edit')
            ->middleware(['web', 'auth']);
    }
);

/**
 * Resources for Ideas
 */
Route::get('ideas/create', 'IdeaController@create')
    ->name('ideas.create')
    ->middleware(['web', 'auth']);

Route::post('ideas', 'IdeaController@store')
    ->name('ideas.store')
    ->middleware(['web', 'auth', 'throttle:product-write']);

Route::get('ideas/{idea}/edit', 'IdeaController@edit')
    ->name('ideas.edit')
    ->middleware(['web', 'auth']);

Route::match(['put', 'patch'], 'ideas/{idea}', 'IdeaController@update')
    ->name('ideas.update')
    ->middleware(['web', 'auth', 'throttle:product-write']);

Route::get('ideas', 'IdeaController@index')
    ->name('ideas.index')
    ->middleware(['web']);

Route::get('ideas/{idea}', 'IdeaController@show')
    ->name('ideas.show')
    ->middleware(['web']);

Route::scopeBindings()->group(function () {
    Route::get('ideas/{idea}/dashboard', 'IdeaController@dashboard')
        ->name('ideas.dashboard')
        ->middleware(['web', 'auth']);

    Route::post('ideas/{idea}/repository-create', 'IdeaController@createRepository')
        ->name('ideas.repository-create')
        ->middleware(['web', 'auth', 'throttle:integration-write']);

    Route::post('ideas/{idea}/repository-invite', 'IdeaController@inviteUsersToRepository')
        ->name('ideas.repository-invite')
        ->middleware(['web', 'auth', 'throttle:integration-write']);

    /**
     * Resources for Idea Comments
     */
    Route::get('ideas/{idea}/comments/create', 'IdeaCommentController@create')
        ->name('ideas.comments.create')
        ->middleware(['web', 'auth']);

    Route::post('ideas/{idea}/comments', 'IdeaCommentController@store')
        ->name('ideas.comments.store')
        ->middleware(['web', 'auth', 'throttle:product-write']);

    Route::get('ideas/{idea}/comments/{comment}/edit', 'IdeaCommentController@edit')
        ->name('ideas.comments.edit')
        ->middleware(['web', 'auth']);

    Route::match(['put', 'patch'], 'ideas/{idea}/comments/{comment}', 'IdeaCommentController@update')
        ->name('ideas.comments.update')
        ->middleware(['web', 'auth', 'throttle:product-write']);

    /**
     * Resources for Idea Supporters
     */
    Route::post('ideas/{idea}/supporters', 'IdeaSupporterController@store')
        ->name('ideas.supporters.store')
        ->middleware(['web', 'auth', 'throttle:product-write']);

    Route::delete('ideas/{idea}/supporters/{supporter}', 'IdeaSupporterController@destroy')
        ->name('ideas.supporters.destroy')
        ->middleware(['web', 'auth', 'throttle:product-write']);

    /**
     * Resources for Idea Applications
     */
    Route::get('ideas/{idea}/applications/create', 'IdeaApplicationController@create')
        ->name('ideas.applications.create')
        ->middleware(['web', 'auth']);

    Route::post('ideas/{idea}/applications', 'IdeaApplicationController@store')
        ->name('ideas.applications.store')
        ->middleware(['web', 'auth', 'throttle:product-write']);

    Route::delete('ideas/{idea}/applications/{application}', 'IdeaApplicationController@destroy')
        ->name('ideas.applications.destroy')
        ->middleware(['web', 'auth', 'throttle:product-write']);

    Route::put('ideas/{idea}/applications/{application}', 'IdeaApplicationController@approveApplication')
        ->name('ideas.applications.approve')
        ->middleware(['web', 'auth', 'throttle:product-write']);
});

/**
 * Social integrations
 */
Route::get('auth/github', 'Auth\SocialController@redirectToProvider')
    ->name('auth.github.login')
    ->middleware(['web', 'auth']);

Route::get('auth/github/callback', 'Auth\SocialController@handleProviderCallback')
    ->name('auth.github.callback')
    ->middleware(['web', 'auth']);

Route::delete('auth/github/revoke', 'Auth\SocialController@revokeProvider')
    ->name('auth.github.revoke')
    ->middleware(['web', 'auth', 'throttle:integration-write']);

/**
 * Resource Links
 */
Route::prefix('resources')->group(
    function () {
        Route::get('feedback', fn () => Inertia\Inertia::render('Feedback'))
            ->name('resources.feedback')
            ->middleware('web');

        Route::get('pricing', fn () => Inertia\Inertia::render('Pricing'))
            ->name('resources.pricing')
            ->middleware('web');
    }
);
