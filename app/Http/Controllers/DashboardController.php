<?php

namespace App\Http\Controllers;

use App\Idea;
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
        
        return view('user.dashboard', compact('ideas'));
    }
}
