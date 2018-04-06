<?php

namespace App\Http\Controllers;

use App\Idea;
use App\IdeaApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreIdeaApplication;

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
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Idea $idea
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function create(Request $request, Idea $idea)
    {
        $this->authorize('createApplication', $idea);

        return view('idea.apply', compact('idea'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreIdeaApplication $request
     * @param  \App\Idea $idea
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(StoreIdeaApplication $request, Idea $idea)
    {
        $this->authorize('storeApplication', $idea);

        $input = $request->validated();
        $input['user_id'] = $request->user()->id;
        $application = $idea->applications()->create($input);

        $application->save();

        return redirect()
            ->route('ideas.show', $ideaId)
            ->with('status', 'Application successfully submitted');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\IdeaApplication $application
     * @return \Illuminate\Http\Response
     */
    public function show(IdeaApplication $application)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\IdeaApplication $application
     * @return \Illuminate\Http\Response
     */
    public function edit(IdeaApplication $application)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\IdeaApplication $application
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, IdeaApplication $application)
    {
        //
    }

    /**
     * Approve the application.
     *
     * @param  \App\Idea $idea
     * @param  \App\IdeaApplication $application
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function approveApplication(Idea $idea, IdeaApplication $application)
    {
        $this->authorizeForUser(Auth::user(), 'updateApplication', $idea);

        $applicantName = "{$application->user->first_name} {$application->user->last_name}";

        $application->status = 'approved';
        $application->save();

        return redirect()
            ->back()
            ->with('status', "You have approved $applicantName");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Idea $idea
     * @param  \App\IdeaApplication $application
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(Idea $idea, IdeaApplication $application)
    {
        $this->authorizeForUser(Auth::user(), 'deleteApplication', $idea);

        $applicantName = "{$application->user->first_name} {$application->user->last_name}";

        $application->delete();

        return redirect()
            ->back()
            ->with('status', "$applicantName is the weakest link, good bye!");
    }
}
