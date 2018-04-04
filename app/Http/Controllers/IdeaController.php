<?php

namespace App\Http\Controllers;

use App\Idea;
use App\Http\Requests\StoreIdea;
use Illuminate\Support\Facades\Auth;

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
            ->paginate(10);

        return view('idea.list', compact('ideas'));
    }

    /**
     * Display the dashboard for an idea
     *
     * @param  \App\Idea $idea
     * @return \Illuminate\Http\Response
     */
    public function dashboard(Idea $idea)
    {
        $applications = $idea->pendingApplications()->get();
        $collaborators = $idea->approvedApplications()->get();

        return view('idea.manage', compact('idea', 'applications', 'collaborators'));
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
     * @param  \App\Http\Requests\StoreIdea $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreIdea $request)
    {
        $user = $request->user();
        $idea = $user->ideas()->create($request->validated());

        return redirect()
            ->route('ideas.show', compact('idea'))
            ->with('status', 'Idea successfully created');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Idea $idea
     * @return \Illuminate\Http\Response
     */
    public function show(Idea $idea)
    {
        if (Auth::user()) {
            $collaborator = $idea->hasApplicationFromUser(Auth::user()->id, 'approved');
            $applicant = $idea->hasApplicationFromUser(Auth::user()->id, 'pending');
            $supporter = $idea->hasSupportFromUser(Auth::user()->id);
        }
        
        return view('idea.single', compact(
                'idea',
                'collaborator',
                'applicant',
                'supporter'
            )
        );
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Idea $idea
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function edit(Idea $idea)
    {
        $this->authorize('manage', $idea);

        return view('idea.edit-add', compact('idea'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\StoreIdea $request
     * @param  \App\Idea $idea
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(StoreIdea $request, Idea $idea)
    {
        $this->authorize('update', $idea);

        $idea->update($request->validated());
        $idea->save();

        return redirect()
            ->route('ideas.show', compact('idea'))
            ->with('status', 'Idea successfully edited');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Idea $idea
     * @return \Illuminate\Http\Response
     */
    public function destroy(Idea $idea)
    {
        //
    }
}
