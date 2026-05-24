<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreIdeaApplication;
use App\Models\Idea;
use App\Models\IdeaApplication;
use App\Models\User;
use App\Services\Ideas\ApplicationService;
use App\Services\Inertia\PagePropsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class IdeaApplicationController extends Controller
{
    public function __construct(
        private ApplicationService $applicationService,
        private PagePropsService $pageProps
    ) {}

    public function create(Idea $idea): Response
    {
        $this->authorize('createApplication', $idea);

        return Inertia::render('Ideas/Apply', [
            'idea' => $this->pageProps->idea($idea),
        ]);
    }

    public function edit(Idea $idea, IdeaApplication $application): Response
    {
        $this->authorize('update', $application);

        return Inertia::render('Ideas/Apply', [
            'idea' => $this->pageProps->idea($idea),
            'application' => $this->pageProps->application($application),
        ]);
    }

    public function store(StoreIdeaApplication $request, Idea $idea): RedirectResponse
    {
        $this->authorize('storeApplication', $idea);

        $this->applicationService->create($idea, $request->toData());

        return redirect()
            ->route('ideas.show', $idea->id)
            ->with('status', 'Application successfully submitted');
    }

    public function update(StoreIdeaApplication $request, Idea $idea, IdeaApplication $application): RedirectResponse
    {
        $this->authorize('update', $application);

        $this->applicationService->update($application, $request->toData());

        return redirect()
            ->route('ideas.show', $idea->id)
            ->with('status', 'Application updated');
    }

    public function approveApplication(Idea $idea, IdeaApplication $application): RedirectResponse
    {
        $this->authorizeForUser(Auth::user(), 'updateApplication', $idea);

        $this->applicationService->approve($application);
        $user = $application->user;
        $applicantName = 'The applicant';

        if ($user instanceof User) {
            $applicantName = "{$user->first_name} {$user->last_name}";
        }

        return redirect()
            ->back()
            ->with('status', "You have approved $applicantName");
    }

    public function destroy(Idea $idea, IdeaApplication $application): RedirectResponse
    {
        $this->authorize('delete', $application);
        $user = Auth::user();

        if ($user instanceof User && (int) $application->user_id === (int) $user->id) {
            $this->applicationService->withdraw($application);

            return redirect()
                ->route('ideas.show', $idea->id)
                ->with('status', 'Application withdrawn');
        }

        $this->applicationService->destroy($application);
        $user = $application->user;
        $applicantName = 'The applicant';

        if ($user instanceof User) {
            $applicantName = "{$user->first_name} {$user->last_name}";
        }

        return redirect()
            ->back()
            ->with('status', "$applicantName has been removed from this idea.");
    }
}
