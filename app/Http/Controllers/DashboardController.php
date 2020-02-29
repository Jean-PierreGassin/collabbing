<?php

namespace App\Http\Controllers;

use App\Models\Idea;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Class DashboardController
 * @package App\Http\Controllers
 */
class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return Factory|View
     */
    public function index()
    {
        $ideas = Auth::user()->ideas()
            ->orderBy('created_at', 'desc')
            ->paginate(5, ['*'], 'ideas');

        $collaborations = Auth::user()->collaborations()->pluck('idea_id');
        $collaborations = Idea::whereIn('id', $collaborations)
            ->paginate(5, ['*'], 'collaborations');

        return view('user.dashboard', compact('ideas', 'collaborations'));
    }
}
