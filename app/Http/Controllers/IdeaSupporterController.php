<?php

namespace App\Http\Controllers;

use App\Idea;
use App\IdeaSupporter;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Class IdeaSupporterController
 * @package App\Http\Controllers
 */
class IdeaSupporterController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(): ?Response
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create(): ?Response
    {
        //
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

        $idea->supporters()->firstOrCreate(
            [
                'user_id' => $request->user()->id,
                'idea_id' => $idea->id,
            ]
        );

        return redirect()
            ->back();
    }

    /**
     * Display the specified resource.
     *
     * @param IdeaSupporter $supporter
     * @return Response
     */
    public function show(IdeaSupporter $supporter): ?Response
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param IdeaSupporter $supporter
     * @return Response
     */
    public function edit(IdeaSupporter $supporter): ?Response
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param IdeaSupporter $supporter
     * @return Response
     */
    public function update(Request $request, IdeaSupporter $supporter): ?Response
    {
        //
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

        try {
            $supporter->delete();
        } catch (Exception $e) {
            //
        }

        return redirect()
            ->back();
    }
}
