<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreIdea;
use App\Models\Idea;
use App\Models\User;
use App\Services\Ideas\ApplicationService;
use App\Services\Ideas\IdeaService;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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

    public function __construct(IdeaService $ideaService, ApplicationService $applicationService)
    {
        $this->ideaService = $ideaService;
        $this->applicationService = $applicationService;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return Factory|View
     */
    public function index(Request $request)
    {
        $searchResults = [];
        if ($search = $request->get('search')) {
            $searchResults = $this->ideaService->searchIdeas($search);
        }

        $trendingIdeas = $this->ideaService->getTrendingIdeas();

        $trendingIds = $trendingIdeas->pluck('id');

        $ideas = $this->ideaService->getIdeas();

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
        if (Auth::user()) {
            $collaborator = $idea->hasApplicationFromUser(Auth::user()->id, 'approved');
            $applicant = $idea->hasApplicationFromUser(Auth::user()->id, 'pending');
            $supporter = $idea->hasSupportFromUser(Auth::user()->id);
        }

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

        $idea->update($request->validated());
        $idea->save();

        return redirect()
            ->route('ideas.show', compact('idea'))
            ->with('status', 'Idea successfully edited');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Idea $idea
     * @return Response
     */
    public function destroy(Idea $idea): ?Response
    {
        //
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

        try {
            $gitHub = Auth::user()->createGithubClient();

            $gitHub->repo()->create($idea->repository_name);
            $idea->repository = 1;
            $idea->save();
        } catch (Exception $e) {
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

        foreach ($idea->approvedApplications()->get() as $collaborator) {
            $this->inviteUserToRepository($idea, $collaborator);
        }

        return redirect()
            ->route('ideas.dashboard', $idea)
            ->with('status', 'All Collaborators has been invited 🤟');
    }

    /**
     * @param Idea $idea
     * @param $collaborator
     * @return bool|RedirectResponse
     * @throws AuthorizationException
     */
    public function inviteUserToRepository(Idea $idea, $collaborator)
    {
        $this->authorize('inviteUsersToRepository', $idea);

        $user = User::find(Auth::user()->id);

        try {
            $gitHub = Auth::user()->createGithubClient();

            $gitHub->repo()->collaborators()->add(
                $user->github_username,
                $idea->repository_name,
                $collaborator->user->github_username
            );
        } catch (Exception $e) {
            return redirect()
                ->route('ideas.dashboard', $idea)
                ->with('status', 'Something went wrong, try again 🙉');
        }


        return true;
    }
}
