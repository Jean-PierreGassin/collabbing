<?php

namespace App\Http\Controllers;

use App\Idea;
use App\Http\Requests\StoreIdea;

class IdeaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $ideas = Idea::where('status', 'open')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('idea.list', compact('ideas'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('idea.edit-add');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreIdea  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreIdea $request)
    {
        $user = $request->user();
        $idea = $user->ideas()->create($request->validated());

        return redirect()
            ->route('ideas.show', compact('idea', $idea))
            ->with('status', 'Idea successfully created');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Idea  $idea
     * @return \Illuminate\Http\Response
     */
    public function show(Idea $idea)
    {
        return view('idea.single', compact('idea', $idea));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Idea  $idea
     * @return \Illuminate\Http\Response
     */
    public function edit(Idea $idea)
    {
        return view('idea.edit-add', compact('idea', $idea));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\StoreIdea  $request
     * @param  \App\Idea  $idea
     * @return \Illuminate\Http\Response
     */
    public function update(StoreIdea $request, Idea $idea)
    {
        $idea->update($request->validated());
        $idea->save();

        return redirect()
            ->route('ideas.show', compact('idea', $idea))
            ->with('status', 'Idea successfully edited');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Idea  $idea
     * @return \Illuminate\Http\Response
     */
    public function destroy(Idea $idea)
    {
        //
    }
}
