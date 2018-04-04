<?php

namespace App\Http\Controllers;

use App\Idea;
use App\IdeaComment;
use App\Http\Requests\StoreIdeaComment;
use Illuminate\Http\Request;

class IdeaCommentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function create(Request $request)
    {
        $idea = Idea::find($request->route()->parameter('idea'));

        $this->authorize('storeComment', $idea);

        return view('comment.add');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreIdeaComment $request
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(StoreIdeaComment $request)
    {
        $idea = Idea::find($request->route()->parameter('idea'));

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
     * @param  \App\IdeaComment $comment
     * @return \Illuminate\Http\Response
     */
    public function show(IdeaComment $comment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Idea $idea
     * @param  \App\IdeaComment $comment
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function edit(Idea $idea, IdeaComment $comment)
    {
        $this->authorize('manage', $comment);

        return view('comment.edit', compact('idea', 'comment'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\StoreIdeaComment $request
     * @param  \App\IdeaComment $comment
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(StoreIdeaComment $request, Idea $idea, IdeaComment $comment)
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
     * @param  \App\IdeaComment $comment
     * @return \Illuminate\Http\Response
     */
    public function destroy(IdeaComment $comment)
    {
        //
    }
}
