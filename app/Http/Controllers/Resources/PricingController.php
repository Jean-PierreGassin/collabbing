<?php

namespace App\Http\Controllers\Resources;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;

/**
 * Class PricingController
 * @package App\Http\Controllers\Resources
 */
class PricingController extends Controller
{
    /**
     * Show the pricing page.
     *
     * @return Factory|View
     */
    public function index()
    {
        return view('resources.pricing');
    }
}
