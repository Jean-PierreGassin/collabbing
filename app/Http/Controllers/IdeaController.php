<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchIdeas;
use App\Http\Requests\StoreIdea;
use App\Http\Requests\UpdateIdeaStatus;
use App\Models\Idea;
use App\Models\IdeaApplication;
use App\Models\IdeaComment;
use App\Services\Ideas\ApplicationService;
use App\Services\Ideas\IdeaService;
use App\Services\Ideas\SupporterService;
use App\Services\Inertia\PagePropsService;
use App\Services\RepositoryService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class IdeaController extends Controller
{
    public function __construct(
        private IdeaService $ideaService,
        private ApplicationService $applicationService,
        private SupporterService $supporterService,
        private RepositoryService $repositoryService,
        private PagePropsService $pageProps
    ) {}

    public function index(SearchIdeas $request): Response
    {
        $searchResults = null;
        $keyword = $request->searchTerm();

        if ($keyword) {
            $searchResults = $this->ideaService->search($keyword);
            $searchResults->appends(['search' => $keyword]);
        }

        $trendingIdeas = $this->ideaService->getTrending();

        $ideas = $this->ideaService->getOpenRecent();
        $searchResultsProps = null;

        if ($searchResults) {
            $searchResultsProps = $this->pageProps->paginator($searchResults, fn (Idea $idea) => $this->pageProps->idea($idea));
        }

        return Inertia::render('Ideas/Index', [
            'keyword' => $keyword,
            'searchResults' => $searchResultsProps,
            'trendingIdeas' => $trendingIdeas->map(fn (Idea $idea) => $this->pageProps->idea($idea))->values(),
            'ideas' => $this->pageProps->paginator($ideas, fn (Idea $idea) => $this->pageProps->idea($idea)),
        ]);
    }

    public function dashboard(Idea $idea): Response
    {
        $this->authorize('manage', $idea);

        $applications = $this->applicationService->getPendingApplications($idea);
        $collaborators = $this->applicationService->getApprovedApplications($idea);

        return Inertia::render('Ideas/Manage', [
            'idea' => $this->pageProps->idea($idea),
            'applications' => $this->pageProps->paginator($applications, fn (IdeaApplication $application) => $this->pageProps->application($application)),
            'collaborators' => $this->pageProps->paginator($collaborators, fn (IdeaApplication $application) => $this->pageProps->application($application)),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Ideas/Form');
    }

    public function store(StoreIdea $request): RedirectResponse
    {
        $idea = $this->ideaService->create($request->toData());

        return redirect()
            ->route('ideas.show', compact('idea'))
            ->with('status', 'Idea successfully created');
    }

    public function show(Idea $idea): Response
    {
        if (! Auth::user()) {
            return Inertia::render('Ideas/Show', [
                'idea' => $this->pageProps->idea($idea),
                'comments' => $this->pageProps->paginator(
                    $this->ideaService->getComments($idea),
                    fn (IdeaComment $comment) => $this->pageProps->comment($comment)
                ),
                'collaborator' => null,
                'applicant' => null,
                'supporter' => null,
            ]);
        }

        $collaborator = $this->applicationService->getApplicationFromUser($idea, 'approved');
        $applicant = $this->applicationService->getApplicationFromUser($idea, 'pending');
        $supporter = $this->supporterService->getSupportFromUser($idea);
        $collaboratorProps = null;
        $applicantProps = null;

        if ($collaborator) {
            $collaboratorProps = $this->pageProps->application($collaborator);
        }

        if ($applicant) {
            $applicantProps = $this->pageProps->application($applicant);
        }

        return Inertia::render('Ideas/Show', [
            'idea' => $this->pageProps->idea($idea),
            'comments' => $this->pageProps->paginator(
                $this->ideaService->getComments($idea),
                fn (IdeaComment $comment) => $this->pageProps->comment($comment)
            ),
            'collaborator' => $collaboratorProps,
            'applicant' => $applicantProps,
            'supporter' => $this->pageProps->supporter($supporter),
        ]);
    }

    public function edit(Idea $idea): Response
    {
        $this->authorize('manage', $idea);

        return Inertia::render('Ideas/Form', [
            'idea' => $this->pageProps->idea($idea),
        ]);
    }

    public function update(StoreIdea $request, Idea $idea): RedirectResponse
    {
        $this->authorize('update', $idea);

        $this->ideaService->update($idea, $request->toData());

        return redirect()
            ->route('ideas.show', compact('idea'))
            ->with('status', 'Idea successfully edited');
    }

    public function updateStatus(UpdateIdeaStatus $request, Idea $idea): RedirectResponse
    {
        $this->authorize('update', $idea);

        $this->ideaService->updateStatus($idea, $request->toData());

        return redirect()
            ->route('ideas.dashboard', compact('idea'))
            ->with('status', 'Idea status updated.');
    }

    public function createRepository(Idea $idea): RedirectResponse
    {
        $this->authorize('createRepository', $idea);

        $idea->loadMissing('codeRepository');

        if ($idea->latestCodeRepository()?->isAvailable()) {
            return redirect()->route('ideas.dashboard', $idea);
        }

        try {
            $this->repositoryService->create($idea);
        } catch (Exception $exception) {
            return redirect()
                ->route('ideas.dashboard', $idea)
                ->withErrors(['repository' => $exception->getMessage()]);
        }

        return redirect()
            ->route('ideas.dashboard', $idea)
            ->with('status', 'Repository created.');
    }

    public function inviteUsersToRepository(Idea $idea): RedirectResponse
    {
        $this->authorize('inviteUsersToRepository', $idea);

        if (! $this->repositoryService->inviteUsers($idea)) {
            return redirect()
                ->route('ideas.dashboard', $idea)
                ->with('status', 'Repository invitations could not be sent. Please try again.');
        }

        return redirect()
            ->route('ideas.dashboard', $idea)
            ->with('status', 'Collaborators have been invited.');
    }
}
