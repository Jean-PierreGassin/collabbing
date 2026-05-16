<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUser;
use App\Models\User;
use App\Services\Inertia\PagePropsService;
use App\Services\UserService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
     *
     * @return Response
     */
    public function index()
    {
        $users = $this->userService->all();

        return Inertia::render('Users/Index', [
            'users' => $users->map(fn (User $user) => $this->pageProps->user($user))->values(),
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @return Response
     */
    public function show(Request $request)
    {
        if ($user = $this->userService->getUserByUsername($request->username)) {
            return Inertia::render('Users/Show', [
                'user' => $this->pageProps->user($user),
            ]);
        }

        abort(404);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return Response
     *
     * @throws AuthorizationException
     */
    public function edit(Request $request)
    {
        $user = $this->userService->getUserByUsername($request->username);
        $this->authorizeForUser(Auth::user(), 'manage', $user);

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
    public function update(StoreUser $request): RedirectResponse
    {
        $user = $this->userService->getUserByUsername($request->username);
        $this->authorizeForUser(Auth::user(), 'update', $user);

        $this->userService->update($user, $request->validated());

        return redirect()
            ->back()
            ->with('status', 'User successfully edited');
    }
}
