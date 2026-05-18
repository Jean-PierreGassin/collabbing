<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreIdeaComment;
use App\Models\Idea;
use App\Models\IdeaComment;
use App\Services\Ideas\CommentService;
use App\Services\Inertia\PagePropsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class IdeaCommentController extends Controller
{
    private CommentService $commentService;

    private PagePropsService $pageProps;

    public function __construct(CommentService $commentService, PagePropsService $pageProps)
    {
        $this->commentService = $commentService;
        $this->pageProps = $pageProps;
    }

    public function create(Request $request, Idea $idea)
    {
        $this->authorize('storeComment', $idea);

        return Inertia::render('Comments/Form', [
            'idea' => $this->pageProps->idea($idea),
        ]);
    }

    public function store(StoreIdeaComment $request, Idea $idea): RedirectResponse
    {
        $this->authorize('storeComment', $idea);

        $this->commentService->store($idea, $request->validated());

        return redirect()
            ->route('ideas.show', compact('idea'))
            ->with('status', 'Comment successfully created');
    }

    public function edit(Idea $idea, IdeaComment $comment)
    {
        $this->authorize('manage', $comment);

        return Inertia::render('Comments/Form', [
            'idea' => $this->pageProps->idea($idea),
            'comment' => $this->pageProps->comment($comment),
        ]);
    }

    public function update(StoreIdeaComment $request, Idea $idea, IdeaComment $comment): RedirectResponse
    {
        $this->authorize('update', $comment);

        $this->commentService->update($comment, $request->safe()->only(['content']));

        return redirect()
            ->route('ideas.show', compact('idea'))
            ->with('status', 'Comment successfully edited');
    }
}
