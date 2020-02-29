<?php

namespace App\Http\Controllers;

use App\Models\Idea;
use App\Models\IdeaSupporter;
use App\Services\Ideas\SupporterService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Class IdeaSupporterController
 * @package App\Http\Controllers
 */
class IdeaSupporterController extends Controller
{
    /**
     * @var SupporterService
     */
    private SupporterService $supporterService;

    public function __construct(SupporterService $supporterService)
    {
        $this->supporterService = $supporterService;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @param Idea $idea
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function store(Request $request, Idea $idea): RedirectResponse
    {
        $this->authorize('storeSupporter', $idea);

        $this->supporterService->create($idea);

        return redirect()
            ->back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Idea $idea
     * @param IdeaSupporter $supporter
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function destroy(Idea $idea, IdeaSupporter $supporter): RedirectResponse
    {
        $this->authorize('delete', $supporter);

        $this->supporterService->destroy($supporter);

        return redirect()
            ->back();
    }
}
