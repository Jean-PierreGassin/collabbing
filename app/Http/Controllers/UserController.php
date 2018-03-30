<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\User  $ideaComment
     * @return \Illuminate\Http\Response
     */
    public function show(User $ideaComment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\User  $ideaComment
     * @return \Illuminate\Http\Response
     */
    public function edit(User $ideaComment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\User  $ideaComment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $ideaComment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\User  $ideaComment
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $ideaComment)
    {
        //
    }
}
