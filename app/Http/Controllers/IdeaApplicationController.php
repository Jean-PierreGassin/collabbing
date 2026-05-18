<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreIdeaApplication;
use App\Models\Idea;
use App\Models\IdeaApplication;
use App\Models\User;
use App\Services\Ideas\ApplicationService;
use App\Services\Inertia\PagePropsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class IdeaApplicationController extends Controller
{
    public function __construct(
        private ApplicationService $applicationService,
        private PagePropsService $pageProps
    ) {}

    public function create(Request $request, Idea $idea): Response
    {
        $this->authorize('createApplication', $idea);

        return Inertia::render('Ideas/Apply', [
            'idea' => $this->pageProps->idea($idea),
        ]);
    }

    public function store(StoreIdeaApplication $request, Idea $idea): RedirectResponse
    {
        $this->authorize('storeApplication', $idea);

        $this->applicationService->create($idea, $request->validated());

        return redirect()
            ->route('ideas.show', $idea->id)
            ->with('status', 'Application successfully submitted');
    }

    public function approveApplication(Idea $idea, IdeaApplication $application): RedirectResponse
    {
        $this->authorizeForUser(Auth::user(), 'updateApplication', $idea);

        $this->applicationService->approve($application);
        $user = $application->user;
        $applicantName = $user instanceof User
            ? "{$user->first_name} {$user->last_name}"
            : 'The applicant';

        return redirect()
            ->back()
            ->with('status', "You have approved $applicantName");
    }

    public function destroy(Idea $idea, IdeaApplication $application): RedirectResponse
    {
        $this->authorizeForUser(Auth::user(), 'deleteApplication', $idea);

        $this->applicationService->destroy($application);
        $user = $application->user;
        $applicantName = $user instanceof User
            ? "{$user->first_name} {$user->last_name}"
            : 'The applicant';

        return redirect()
            ->back()
            ->with('status', "$applicantName has been removed from this idea.");
    }
}
