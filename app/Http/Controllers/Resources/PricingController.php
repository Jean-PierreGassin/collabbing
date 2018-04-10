<?php

namespace App\Http\Controllers\Resources;

use App\Http\Controllers\Controller;

class PricingController extends Controller
{
    /**
     * Show the pricing page.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('resources.pricing');
    }
}
