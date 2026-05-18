<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchIdeas;
use App\Models\Idea;
use App\Services\Ideas\IdeaService;
use App\Services\Inertia\PagePropsService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private IdeaService $ideaService,
        private PagePropsService $pageProps
    ) {
        $this->middleware('auth');
    }

    public function index(SearchIdeas $request): Response
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
