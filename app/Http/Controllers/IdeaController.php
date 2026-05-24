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
        $tag = $request->tag();

        if ($keyword || $tag) {
            $searchResults = $this->ideaService->browseOpen($keyword, $tag);
            $searchResults->appends($this->searchAppends($keyword, $tag));
        }

        $trendingIdeas = $this->ideaService->getTrending();
        $popularTags = $this->ideaService->getPopularTags();

        $ideas = $this->ideaService->getOpenRecent();
        $searchResultsProps = null;

        if ($searchResults) {
            $searchResultsProps = $this->pageProps->paginator($searchResults, fn (Idea $idea) => $this->pageProps->idea($idea));
        }

        return Inertia::render('Ideas/Index', [
            'keyword' => $keyword,
            'selectedTag' => $tag,
            'popularTags' => $popularTags,
            'searchResults' => $searchResultsProps,
            'trendingIdeas' => $trendingIdeas->map(fn (Idea $idea) => $this->pageProps->idea($idea))->values(),
            'ideas' => $this->pageProps->paginator($ideas, fn (Idea $idea) => $this->pageProps->idea($idea)),
        ]);
    }

    private function searchAppends(?string $keyword, ?string $tag): array
    {
        $appends = [];

        if ($keyword) {
            $appends['search'] = $keyword;
        }

        if ($tag) {
            $appends['tag'] = $tag;
        }

        return $appends;
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
                'historicalApplication' => null,
                'supporter' => null,
            ]);
        }

        $collaborator = $this->applicationService->getApplicationFromUser($idea, 'approved');
        $applicant = $this->applicationService->getApplicationFromUser($idea, 'pending');
        $historicalApplication = null;
        $supporter = $this->supporterService->getSupportFromUser($idea);
        $collaboratorProps = null;
        $applicantProps = null;
        $historicalApplicationProps = null;

        if ($collaborator) {
            $collaboratorProps = $this->pageProps->application($collaborator);
        }

        if ($applicant) {
            $applicantProps = $this->pageProps->application($applicant);
        }

        if (! $collaborator && ! $applicant) {
            $historicalApplication = $this->applicationService->getLatestFinalApplicationFromUser($idea);
        }

        if ($historicalApplication) {
            $historicalApplicationProps = $this->pageProps->application($historicalApplication);
        }

        return Inertia::render('Ideas/Show', [
            'idea' => $this->pageProps->idea($idea),
            'comments' => $this->pageProps->paginator(
                $this->ideaService->getComments($idea),
                fn (IdeaComment $comment) => $this->pageProps->comment($comment)
            ),
            'collaborator' => $collaboratorProps,
            'applicant' => $applicantProps,
            'historicalApplication' => $historicalApplicationProps,
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
