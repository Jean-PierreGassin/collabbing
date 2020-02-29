<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreIdeaApplication;
use App\Idea;
use App\IdeaApplication;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Class IdeaApplicationController
 * @package App\Http\Controllers
 */
class IdeaApplicationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(): ?Response
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param Request $request
     * @param Idea $idea
     * @return Factory|View
     * @throws AuthorizationException
     */
    public function create(Request $request, Idea $idea)
    {
        $this->authorize('createApplication', $idea);

        return view('idea.apply', compact('idea'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreIdeaApplication $request
     * @param Idea $idea
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function store(StoreIdeaApplication $request, Idea $idea)
    {
        $this->authorize('storeApplication', $idea);

        $input = $request->validated();
        $input['user_id'] = $request->user()->id;
        $application = $idea->applications()->create($input);

        $application->save();

        return redirect()
            ->route('ideas.show', $idea->id)
            ->with('status', 'Application successfully submitted');
    }

    /**
     * Display the specified resource.
     *
     * @param IdeaApplication $application
     * @return Response
     */
    public function show(IdeaApplication $application): ?Response
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param IdeaApplication $application
     * @return Response
     */
    public function edit(IdeaApplication $application): ?Response
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param IdeaApplication $application
     * @return Response
     */
    public function update(Request $request, IdeaApplication $application): ?Response
    {
        //
    }

    /**
     * Approve the application.
     *
     * @param Idea $idea
     * @param IdeaApplication $application
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function approveApplication(Idea $idea, IdeaApplication $application): RedirectResponse
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
     * @param Idea $idea
     * @param IdeaApplication $application
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function destroy(Idea $idea, IdeaApplication $application): RedirectResponse
    {
        $this->authorizeForUser(Auth::user(), 'deleteApplication', $idea);

        $applicantName = "{$application->user->first_name} {$application->user->last_name}";

        $application->delete();

        return redirect()
            ->back()
            ->with('status', "$applicantName is the weakest link, good bye!");
    }
}
