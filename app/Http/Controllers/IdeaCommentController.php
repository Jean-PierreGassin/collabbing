<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreIdeaComment;
use App\Idea;
use App\IdeaComment;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

/**
 * Class IdeaCommentController
 * @package App\Http\Controllers
 */
class IdeaCommentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return void
     */
    public function index(): void
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param Request $request
     * @param Idea $idea
     * @return Factory|View
     * @throws AuthorizationException
     */
    public function create(Request $request, Idea $idea)
    {
        $this->authorize('storeComment', $idea);

        return view('comment.add');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreIdeaComment $request
     * @param Idea $idea
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function store(StoreIdeaComment $request, Idea $idea): RedirectResponse
    {
        $this->authorize('storeComment', $idea);

        $input = $request->validated();
        $input['user_id'] = $request->user()->id;

        $idea->comments()->create($input);

        return redirect()
            ->route('ideas.show', compact('idea'))
            ->with('status', 'Comment successfully created');
    }

    /**
     * Display the specified resource.
     *
     * @param IdeaComment $comment
     * @return Response
     */
    public function show(IdeaComment $comment): ?Response
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Idea $idea
     * @param IdeaComment $comment
     * @return Factory|View
     * @throws AuthorizationException
     */
    public function edit(Idea $idea, IdeaComment $comment)
    {
        $this->authorize('manage', $comment);

        return view('comment.edit', compact('idea', 'comment'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param StoreIdeaComment $request
     * @param Idea $idea
     * @param IdeaComment $comment
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function update(StoreIdeaComment $request, Idea $idea, IdeaComment $comment): RedirectResponse
    {
        $this->authorize('update', $comment);

        $comment->update($request->validated());
        $comment->save();

        return redirect()
            ->route('ideas.show', compact('idea'))
            ->with('status', 'Comment successfully edited');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param IdeaComment $comment
     * @return Response
     */
    public function destroy(IdeaComment $comment): ?Response
    {
        //
    }
}
