<?php

namespace App\Http\Controllers;

use App\Models\Idea;
use App\Models\IdeaSupporter;
use App\Services\Ideas\SupporterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class IdeaSupporterController extends Controller
{
    public function __construct(private SupporterService $supporterService) {}

    public function store(Request $request, Idea $idea): RedirectResponse
    {
        $this->authorize('storeSupporter', $idea);

        $this->supporterService->create($idea);

        return redirect()
            ->back();
    }

    public function destroy(Idea $idea, IdeaSupporter $supporter): RedirectResponse
    {
        $this->authorize('delete', $supporter);

        $this->supporterService->destroy($supporter);

        return redirect()
            ->back();
    }
}
