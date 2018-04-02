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

Route::get('/', function () {
    return view('welcome');
});

/**
 * Resources for Users
 */
Route::get('/dashboard', 'DashboardController@index')
    ->name('dashboard');

Route::prefix('users')->group(function () {
    Auth::routes();

    Route::get('{username}', 'UserController@show')
        ->name('users.show')
        ->middleware(['web']);

    Route::put('{username}', 'UserController@update')
        ->name('users.update')
        ->middleware(['web', 'auth']);

    Route::get('{username}/edit', 'UserController@edit')
        ->name('users.edit')
        ->middleware(['web', 'auth']);
});

/**
 * Resources for Ideas
 */
Route::resource('ideas', 'IdeaController')->only([
    'create',
    'store',
    'edit',
    'destroy',
    'update',
])->middleware(['web', 'auth']);

Route::resource('ideas', 'IdeaController')->only([
    'show',
    'index',
])->middleware(['web']);

Route::get('ideas/{idea}/dashboard', 'IdeaController@dashboard')
    ->name('ideas.dashboard')
    ->middleware(['web', 'auth']);

/**
 * Resources for Idea Comments
 */
Route::resource('ideas.comments', 'IdeaCommentController')->only([
    'store',
    'destroy',
])->middleware(['web', 'auth']);

Route::resource('ideas.comments', 'IdeaCommentController')->only([
    'index',
    'show',
])->middleware(['web']);

/**
 * Resources for Idea Supporters
 */
Route::resource('ideas.supporters', 'IdeaSupporterController')->only([
    'store',
    'destroy',
])->middleware(['web', 'auth']);

Route::resource('ideas.supporters', 'IdeaSupporterController')->only([
    'index',
    'show',
])->middleware(['web']);

/**
 * Resources for Idea Applications
 */
Route::resource('ideas.applications', 'IdeaApplicationController')->only([
    'create',
    'store',
    'edit',
    'update',
    'destroy',
])->middleware(['web', 'auth']);

Route::resource('ideas.applications', 'IdeaApplicationController')->only([
    'index',
    'show',
])->middleware(['web']);

Route::put('ideas/{idea}/applications/{application}', 'IdeaApplicationController@approveApplication')
    ->name('ideas.applications.approve')
    ->middleware(['web', 'auth']);