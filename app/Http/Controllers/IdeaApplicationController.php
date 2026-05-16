<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreIdeaApplication;
use App\Models\Idea;
use App\Models\IdeaApplication;
use App\Services\Ideas\ApplicationService;
use App\Services\Ideas\IdeaService;
use App\Services\Inertia\PagePropsService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Class IdeaApplicationController
 */
class IdeaApplicationController extends Controller
{
    private IdeaService $ideaService;

    private ApplicationService $applicationService;

    private PagePropsService $pageProps;

    /**
     * IdeaApplicationController constructor.
     */
    public function __construct(ApplicationService $applicationService, IdeaService $ideaService, PagePropsService $pageProps)
    {
        $this->ideaService = $ideaService;
        $this->applicationService = $applicationService;
        $this->pageProps = $pageProps;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     *
     * @throws AuthorizationException
     */
    public function create(Request $request, Idea $idea)
    {
        $this->authorize('createApplication', $idea);

        return Inertia::render('Ideas/Apply', [
            'idea' => $this->pageProps->idea($idea),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return RedirectResponse
     *
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
