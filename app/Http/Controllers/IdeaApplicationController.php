<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreIdeaApplication;
use App\Idea;
use App\IdeaApplication;
use Illuminate\Http\Request;

class IdeaApplicationController extends Controller
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
    public function create(Request $request)
    {
        $idea = Idea::find($request->route()->parameter('idea'));

        return view('idea.apply', compact('idea'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreIdeaApplication $request)
    {
        $idea = Idea::find($request->route()->parameter('idea'));

        $input = $request->validated();
        $input['user_id'] = $request->user()->id;
        $application = $idea->applications()->create($input);

        $application->save();

        return redirect()
            ->route('ideas.show', $request->route()->parameter('idea'))
            ->with('status', 'Application successfully submitted');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\IdeaApplication  $ideaApplication
     * @return \Illuminate\Http\Response
     */
    public function show(IdeaApplication $ideaApplication)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\IdeaApplication  $ideaApplication
     * @return \Illuminate\Http\Response
     */
    public function edit(IdeaApplication $ideaApplication)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\IdeaApplication  $ideaApplication
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, IdeaApplication $ideaApplication)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\IdeaApplication  $ideaApplication
     * @return \Illuminate\Http\Response
     */
    public function destroy(IdeaApplication $ideaApplication)
    {
        //
    }
}
