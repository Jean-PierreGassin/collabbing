<?php

namespace App\Http\Controllers;

use App\Services\Ideas\IdeaService;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;

/**
 * Class DashboardController
 */
class DashboardController extends Controller
{
    private IdeaService $ideaService;

    /**
     * Create a new controller instance.
     */
    public function __construct(IdeaService $ideaService)
    {
        $this->middleware('auth');

        $this->ideaService = $ideaService;
    }

    /**
     * Show the application dashboard.
     *
     * @return Factory|View
     */
    public function index()
    {
        $ideas = $this->ideaService->getUserIdeas();
        $collaborations = $this->ideaService->getCollaboratedIdeas();

        return view('user.dashboard', compact('ideas', 'collaborations'));
    }
}
