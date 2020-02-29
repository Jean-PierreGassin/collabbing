<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUser;
use App\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

/**
 * Class UserController
 * @package App\Http\Controllers
 */
class UserController extends Controller
{
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
     * @param Request $request
     * @return Factory|View
     */
    public function show(Request $request)
    {
        $user = User::where('username', $request->username)->first();

        if (!$user) {
            return view('errors.404');
        }

        return view('user.single', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Request $request
     * @return Factory|View
     * @throws AuthorizationException
     */
    public function edit(Request $request)
    {
        $user = User::where('username', $request->username)->first();
        $this->authorizeForUser(Auth::user(), 'manage', $user);

        if (!$user) {
            return view('errors.404');
        }

        return view('user.edit-add', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param StoreUser $request
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function update(StoreUser $request): RedirectResponse
    {
        $user = User::where('username', $request->username)->first();
        $this->authorizeForUser(Auth::user(), 'update', $user);

        if (!$user) {
            return view('errors.404');
        }

        foreach ($request->validated() as $key => $value) {
            if ($key === 'password' && is_null($value)) {
                unset($key);
                continue;
            }

            if ($key === 'password' && !is_null($value)) {
                $value = Hash::make($value);
            }

            $user->{$key} = $value;
        }

        $user->save();

        return redirect()
            ->back()
            ->with('status', 'User successfully edited');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param User $user
     * @return Response
     */
    public function destroy(User $user): ?Response
    {
        //
    }
}
