<?php

namespace App\Http\Controllers\Resources;

use App\Http\Controllers\Controller;

class FeedbackController extends Controller
{
    /**
     * Show the feedback page.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('resources.feedback');
    }
}
