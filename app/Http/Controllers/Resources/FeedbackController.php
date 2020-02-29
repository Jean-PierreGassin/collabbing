<?php

namespace App\Http\Controllers\Resources;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;

/**
 * Class FeedbackController
 * @package App\Http\Controllers\Resources
 */
class FeedbackController extends Controller
{
    /**
     * Show the feedback page.
     *
     * @return Factory|View
     */
    public function index()
    {
        return view('resources.feedback');
    }
}
