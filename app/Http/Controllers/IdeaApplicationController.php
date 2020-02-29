<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreIdeaApplication;
use App\Models\Idea;
use App\Models\IdeaApplication;
use App\Services\Ideas\ApplicationService;
use App\Services\Ideas\IdeaService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Class IdeaApplicationController
 * @package App\Http\Controllers
 */
class IdeaApplicationController extends Controller
{
    /**
     * @var IdeaService
     */
    private IdeaService $ideaService;

    /**
     * @var ApplicationService
     */
    private ApplicationService $applicationService;

    /**
     * IdeaApplicationController constructor.
     * @param ApplicationService $applicationService
     * @param IdeaService $ideaService
     */
    public function __construct(ApplicationService $applicationService, IdeaService $ideaService)
    {
        $this->ideaService = $ideaService;
        $this->applicationService = $applicationService;
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

        $this->applicationService->create($idea, $request->validated());

        return redirect()
            ->route('ideas.show', $idea->id)
            ->with('status', 'Application successfully submitted');
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

        $this->applicationService->approve($application);
        $applicantName = "{$application->user->first_name} {$application->user->last_name}";

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

        $this->applicationService->destroy($application);
        $applicantName = "{$application->user->first_name} {$application->user->last_name}";

        return redirect()
            ->back()
            ->with('status', "$applicantName is the weakest link, good bye!");
    }
}
