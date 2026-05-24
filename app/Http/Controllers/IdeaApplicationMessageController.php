<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreIdeaApplicationMessage;
use App\Models\Idea;
use App\Models\IdeaApplication;
use App\Services\Ideas\ApplicationService;
use Illuminate\Http\RedirectResponse;

class IdeaApplicationMessageController extends Controller
{
    public function __construct(private ApplicationService $applicationService) {}

    public function store(StoreIdeaApplicationMessage $request, Idea $idea, IdeaApplication $application): RedirectResponse
    {
        $this->authorize('message', $application);

        $this->applicationService->message($application, $request->toData());

        return redirect()
            ->back()
            ->with('status', 'Message sent.');
    }
}
