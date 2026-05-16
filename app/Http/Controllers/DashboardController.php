<?php

namespace App\Http\Controllers;

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
    public function index()
    {
        $ideas = $this->ideaService->getUserIdeas();
        $collaborations = $this->ideaService->getCollaboratedIdeas();

        return Inertia::render('Dashboard', [
            'ideas' => $ideas->map(fn (Idea $idea) => $this->pageProps->idea($idea))->values(),
            'collaborations' => $collaborations->map(fn (Idea $idea) => $this->pageProps->idea($idea))->values(),
        ]);
    }
}
