<?php

namespace App\Http\Controllers;

use App\Idea;
use App\IdeaApplication;
use Illuminate\Support\Facades\Auth;

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
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $ideas = Auth::user()->ideas()
            ->orderBy('created_at', 'desc')
            ->paginate(5, ['*'], 'ideas');

        $collaborations = Auth::user()->collaborations()
            ->with('idea')
            ->paginate(5, ['*'], 'collaborations');

        return view('user.dashboard', compact('ideas', 'collaborations'));
    }
}
