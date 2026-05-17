<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchIdeas;
use App\Http\Requests\StoreIdea;
use App\Models\Idea;
use App\Models\IdeaApplication;
use App\Models\IdeaComment;
use App\Services\Ideas\ApplicationService;
use App\Services\Ideas\IdeaService;
use App\Services\Ideas\SupporterService;
use App\Services\Inertia\PagePropsService;
use App\Services\RepositoryService;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Class IdeaController
 */
class IdeaController extends Controller
{
    private IdeaService $ideaService;

    private ApplicationService $applicationService;

    private SupporterService $supporterService;

    private RepositoryService $repositoryService;

    private PagePropsService $pageProps;

    public function __construct(
        IdeaService $ideaService,
        ApplicationService $applicationService,
        SupporterService $supporterService,
        RepositoryService $repositoryService,
        PagePropsService $pageProps
    ) {
        $this->ideaService = $ideaService;
        $this->applicationService = $applicationService;
        $this->supporterService = $supporterService;
        $this->repositoryService = $repositoryService;
        $this->pageProps = $pageProps;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(SearchIdeas $request)
    {
        $searchResults = null;
        $keyword = $request->searchTerm();

        if ($keyword) {
            $searchResults = $this->ideaService->search($keyword);
            $searchResults->appends(['search' => $keyword]);
        }

        $trendingIdeas = $this->ideaService->getTrending();

        $ideas = $this->ideaService->getOpenRecent();

        return Inertia::render('Ideas/Index', [
            'keyword' => $keyword,
            'searchResults' => $searchResults
                ? $this->pageProps->paginator($searchResults, fn (Idea $idea) => $this->pageProps->idea($idea))
                : null,
            'trendingIdeas' => $trendingIdeas->map(fn (Idea $idea) => $this->pageProps->idea($idea))->values(),
            'ideas' => $this->pageProps->paginator($ideas, fn (Idea $idea) => $this->pageProps->idea($idea)),
        ]);
    }

    /**
     * Display the dashboard for an idea
     *
     * @return Response
     */
    public function dashboard(Idea $idea)
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

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        return Inertia::render('Ideas/Form');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreIdea $request): RedirectResponse
    {
        $idea = $this->ideaService->create($request->validated());

        return redirect()
            ->route('ideas.show', compact('idea'))
            ->with('status', 'Idea successfully created');
    }

    /**
     * Display the specified resource.
     *
     * @return Response
     */
    public function show(Idea $idea)
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

        return Inertia::render('Ideas/Show', [
            'idea' => $this->pageProps->idea($idea),
            'comments' => $this->pageProps->paginator(
                $this->ideaService->getComments($idea),
                fn (IdeaComment $comment) => $this->pageProps->comment($comment)
            ),
            'collaborator' => $collaborator ? $this->pageProps->application($collaborator) : null,
            'applicant' => $applicant ? $this->pageProps->application($applicant) : null,
            'supporter' => $this->pageProps->supporter($supporter),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return Response
     *
     * @throws AuthorizationException
     */
    public function edit(Idea $idea)
    {
        $this->authorize('manage', $idea);

        return Inertia::render('Ideas/Form', [
            'idea' => $this->pageProps->idea($idea),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @throws AuthorizationException
     */
    public function update(StoreIdea $request, Idea $idea): RedirectResponse
    {
        $this->authorize('update', $idea);

        $this->ideaService->update($idea, $request->validated());

        return redirect()
            ->route('ideas.show', compact('idea'))
            ->with('status', 'Idea successfully edited');
    }

    /**
     * @throws AuthorizationException
     */
    public function createRepository(Idea $idea): RedirectResponse
    {
        $this->authorize('createRepository', $idea);

        $idea->loadMissing('codeRepository');

        if ($idea->codeRepository?->isAvailable()) {
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

    /**
     * @throws AuthorizationException
     */
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
