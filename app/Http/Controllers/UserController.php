<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use App\Http\Requests\StoreUser;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = User::all();

        return view('user.list', compact('users'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
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
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request)
    {
        $user = User::where('username', $request->username)->first();

        if (!$user) {
            return view('errors.404');
        }

        return view('user.edit-add', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\StoreUser $request
     * @return \Illuminate\Http\Response
     */
    public function update(StoreUser $request)
    {
        $user = User::where('username', $request->username)->first();

        if (!$user) {
            return view('errors.404');
        }

        foreach ($request->validated() as $key => $value) {
            if ($key === 'password') {
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
     * @param  \App\User $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        //
    }
}
