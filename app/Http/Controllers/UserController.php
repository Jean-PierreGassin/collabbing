<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUser;
use App\Models\User;
use App\Services\Inertia\PagePropsService;
use App\Services\UserService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Class UserController
 */
class UserController extends Controller
{
    private UserService $userService;

    private PagePropsService $pageProps;

    public function __construct(UserService $userService, PagePropsService $pageProps)
    {
        $this->userService = $userService;
        $this->pageProps = $pageProps;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $users = $this->userService->all();

        return Inertia::render('Users/Index', [
            'users' => $this->pageProps->paginator($users, fn (User $user) => $this->pageProps->user($user)),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user): Response
    {
        return Inertia::render('Users/Show', [
            'user' => $this->pageProps->user($user),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @throws AuthorizationException
     */
    public function edit(User $user): Response
    {
        $this->authorize('manage', $user);

        return Inertia::render('Users/Form', [
            'user' => $this->pageProps->user($user),
            'githubClientId' => config('services.github.client_id'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @throws AuthorizationException
     */
    public function update(StoreUser $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $this->userService->update($user, $request->validated());

        return redirect()
            ->back()
            ->with('status', 'User successfully edited');
    }
}
