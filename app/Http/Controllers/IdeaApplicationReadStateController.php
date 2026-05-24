<?php

namespace App\Http\Controllers;

use App\Models\Idea;
use App\Models\IdeaApplication;
use App\Services\Ideas\ApplicationService;
use Illuminate\Http\Response;

class IdeaApplicationReadStateController extends Controller
{
    public function __construct(private ApplicationService $applicationService) {}

    public function update(Idea $idea, IdeaApplication $application): Response
    {
        $this->authorize('viewThread', $application);

        $this->applicationService->markThreadRead($application);

        return response()->noContent();
    }
}
