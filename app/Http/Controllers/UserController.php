<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUser;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Class UserController
 */
class UserController extends Controller
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Factory|View
     */
    public function index()
    {
        $users = User::all();

        return view('user.list', compact('users'));
    }

    /**
     * Display the specified resource.
     *
     * @return Factory|View
     */
    public function show(Request $request)
    {
        if ($user = $this->userService->getUserByUsername($request->username)) {
            return view('user.single', compact('user'));
        }

        return view('errors.404');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return Factory|View
     *
     * @throws AuthorizationException
     */
    public function edit(Request $request)
    {
        $user = $this->userService->getUserByUsername($request->username);
        $this->authorizeForUser(Auth::user(), 'manage', $user);

        return view('user.edit-add', compact('user'));
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
