<?php

namespace App\Http\Controllers;

use App\Idea;
use App\IdeaSupporter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IdeaSupporterController extends Controller
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
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(Request $request)
    {
        $idea = Idea::find($request->route()->parameter('idea'));
        $this->authorize('storeSupporter', $idea);

        $idea->supporters()->firstOrCreate([
            'user_id' => $request->user()->id,
            'idea_id' => $idea->id,
        ]);

        return redirect()
            ->back();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\IdeaSupporter $ideaSupporter
     * @return \Illuminate\Http\Response
     */
    public function show(IdeaSupporter $ideaSupporter)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\IdeaSupporter $ideaSupporter
     * @return \Illuminate\Http\Response
     */
    public function edit(IdeaSupporter $ideaSupporter)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\IdeaSupporter $ideaSupporter
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, IdeaSupporter $ideaSupporter)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Idea $idea
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(Idea $idea)
    {
        $supporter = $idea->supporters->where('user_id', Auth::user()->id)->first();
        $this->authorize('delete', $supporter);

        $supporter->delete();

        return redirect()
            ->back();
    }
}
