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
        $ideas = Idea::where('user_id', Auth::user()->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $applications = IdeaApplication::where('user_id', Auth::user()->id)
            ->where('status', 'approved')
            ->orderBy('created_at', 'desc')
            ->get();

        $collaborations = [];

        foreach ($applications as $application) {
            $collaborations[] = $application->idea()
                ->where('status', 'open')
                ->first();
        }

        return view('user.dashboard', compact('ideas', 'collaborations'));
    }
}
