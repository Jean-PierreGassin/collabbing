<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreIdeaComment;
use App\Models\Idea;
use App\Models\IdeaComment;
use App\Services\Ideas\CommentService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Class IdeaCommentController
 * @package App\Http\Controllers
 */
class IdeaCommentController extends Controller
{
    /**
     * @var CommentService
     */
    private CommentService $commentService;

    /**
     * IdeaCommentController constructor.
     * @param CommentService $commentService
     */
    public function __construct(CommentService $commentService)
    {
        $this->commentService = $commentService;
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

        $this->commentService->store($idea, $request->validated());

        return redirect()
            ->route('ideas.show', compact('idea'))
            ->with('status', 'Comment successfully created');
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

        $this->commentService->update($comment, $request->validated());

        return redirect()
            ->route('ideas.show', compact('idea'))
            ->with('status', 'Comment successfully edited');
    }
}
