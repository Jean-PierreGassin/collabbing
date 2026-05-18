<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserProfileRequest;
use App\Models\User;
use App\Services\Inertia\PagePropsService;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function __construct(
        private UserService $userService,
        private PagePropsService $pageProps
    ) {}

    public function index(): Response
    {
        $users = $this->userService->all();

        return Inertia::render('Users/Index', [
            'users' => $this->pageProps->paginator($users, fn (User $user) => $this->pageProps->user($user)),
        ]);
    }

    public function show(User $user): Response
    {
        return Inertia::render('Users/Show', [
            'user' => $this->pageProps->user($user),
        ]);
    }

    public function edit(User $user): Response
    {
        $this->authorize('manage', $user);

        return Inertia::render('Users/Form', [
            'user' => $this->pageProps->user($user),
            'githubClientId' => config('services.github.client_id'),
        ]);
    }

    public function update(UpdateUserProfileRequest $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $this->userService->update($user, $request->toData());

        return redirect()
            ->back()
            ->with('status', 'User successfully edited');
    }
}
