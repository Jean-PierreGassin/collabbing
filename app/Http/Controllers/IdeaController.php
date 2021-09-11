<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreIdea;
use App\Models\Idea;
use App\Services\Ideas\ApplicationService;
use App\Services\Ideas\IdeaService;
use App\Services\Ideas\SupporterService;
use App\Services\RepositoryService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Class IdeaController
 * @package App\Http\Controllers
 */
class IdeaController extends Controller
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
     * @var SupporterService
     */
    private SupporterService $supporterService;

    /**
     * @var RepositoryService
     */
    private RepositoryService $repositoryService;

    public function __construct(
        IdeaService $ideaService,
        ApplicationService $applicationService,
        SupporterService $supporterService,
        RepositoryService $repositoryService
    ) {
        $this->ideaService = $ideaService;
        $this->applicationService = $applicationService;
        $this->supporterService = $supporterService;
        $this->repositoryService = $repositoryService;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return Factory|View
     */
    public function index(Request $request)
    {
        $searchResults = null;
        if ($search = $request->get('search')) {
            $searchResults = $this->ideaService->search($search);
        }

        $trendingIdeas = $this->ideaService->getTrending();

        $trendingIds = $trendingIdeas->pluck('id');

        $ideas = Idea::where('status', 'open')
            ->orderBy('created_at', 'desc')
            ->whereNotIn('id', $trendingIds)
            ->paginate(10);

        return view('idea.list', compact('searchResults', 'trendingIdeas', 'ideas'));
    }

    /**
     * Display the dashboard for an idea
     *
     * @param Idea $idea
     * @return Factory|View
     */
    public function dashboard(Idea $idea)
    {
        $applications = $this->applicationService->getPendingApplications($idea);
        $collaborators = $this->applicationService->getApprovedApplications($idea);

        return view(
            'idea.manage',
            compact(
                'idea',
                'applications',
                'collaborators'
            )
        );
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Factory|View
     */
    public function create()
    {
        return view('idea.edit-add');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreIdea $request
     * @return RedirectResponse
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
     * @param Idea $idea
     * @return Factory|View
     */
    public function show(Idea $idea)
    {
        if (!Auth::user()) {
            return view('idea.single', compact('idea'));
        }

        $collaborator = $this->applicationService->getApplicationFromUser($idea, 'approved');
        $applicant = $this->applicationService->getApplicationFromUser($idea, 'pending');
        $supporter = $this->supporterService->getSupportFromUser($idea);

        return view(
            'idea.single',
            compact(
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
     * @param Idea $idea
     * @return Factory|View
     * @throws AuthorizationException
     */
    public function edit(Idea $idea)
    {
        $this->authorize('manage', $idea);

        return view('idea.edit-add', compact('idea'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param StoreIdea $request
     * @param Idea $idea
     * @return RedirectResponse
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
     * @param Idea $idea
     * @return RedirectResponse|string
     * @throws AuthorizationException
     */
    public function createRepository(Idea $idea)
    {
        $this->authorize('createRepository', $idea);

        if ($idea->repository) {
            return route('ideas.dashboard', $idea);
        }

        if (!$this->repositoryService->create($idea)) {
            return redirect()
                ->route('ideas.dashboard', $idea)
                ->with('status', 'Something went wrong, try again 🙉');
        }

        return redirect()
            ->route('ideas.dashboard', $idea)
            ->with('status', 'Repository created 🔥');
    }

    /**
     * @param Idea $idea
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function inviteUsersToRepository(Idea $idea): RedirectResponse
    {
        $this->authorize('inviteUsersToRepository', $idea);

        if (!$this->repositoryService->inviteUsers($idea)) {
            return redirect()
                ->route('ideas.dashboard', $idea)
                ->with('status', 'Something went wrong, try again 🙉');
        }

        return redirect()
            ->route('ideas.dashboard', $idea)
            ->with('status', 'All Collaborators has been invited 🤟');
    }
}
