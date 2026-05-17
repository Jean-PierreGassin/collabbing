<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchIdeas;
use App\Models\Idea;
use App\Services\Ideas\IdeaService;
use App\Services\Inertia\PagePropsService;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Class DashboardController
 */
class DashboardController extends Controller
{
    private IdeaService $ideaService;

    private PagePropsService $pageProps;

    /**
     * Create a new controller instance.
     */
    public function __construct(IdeaService $ideaService, PagePropsService $pageProps)
    {
        $this->middleware('auth');

        $this->ideaService = $ideaService;
        $this->pageProps = $pageProps;
    }

    /**
     * Show the application dashboard.
     *
     * @return Response
     */
    public function index(SearchIdeas $request)
    {
        $keyword = $request->searchTerm();
        $ideas = $this->ideaService->getUserIdeas($keyword);
        $collaborations = $this->ideaService->getCollaboratedIdeas($keyword);

        if ($keyword) {
            $ideas->appends(['search' => $keyword]);
            $collaborations->appends(['search' => $keyword]);
        }

        return Inertia::render('Dashboard', [
            'keyword' => $keyword,
            'ideas' => $this->pageProps->paginator($ideas, fn (Idea $idea) => $this->pageProps->idea($idea)),
            'collaborations' => $this->pageProps->paginator($collaborations, fn (Idea $idea) => $this->pageProps->idea($idea)),
        ]);
    }
}
