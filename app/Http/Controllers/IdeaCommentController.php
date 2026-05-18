<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreIdeaComment;
use App\Models\Idea;
use App\Models\IdeaComment;
use App\Services\Ideas\CommentService;
use App\Services\Inertia\PagePropsService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class IdeaCommentController extends Controller
{
    public function __construct(
        private CommentService $commentService,
        private PagePropsService $pageProps
    ) {}

    public function create(Idea $idea): Response
    {
        $this->authorize('storeComment', $idea);

        return Inertia::render('Comments/Form', [
            'idea' => $this->pageProps->idea($idea),
        ]);
    }

    public function store(StoreIdeaComment $request, Idea $idea): RedirectResponse
    {
        $this->authorize('storeComment', $idea);

        $this->commentService->store($idea, $request->toData());

        return redirect()
            ->route('ideas.show', compact('idea'))
            ->with('status', 'Comment successfully created');
    }

    public function edit(Idea $idea, IdeaComment $comment): Response
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

        $this->commentService->update($comment, $request->toData());

        return redirect()
            ->route('ideas.show', compact('idea'))
            ->with('status', 'Comment successfully edited');
    }
}
