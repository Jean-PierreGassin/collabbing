<?php

namespace App\Services\Ideas;

use App\Models\Idea;
use App\Models\IdeaComment;
use Illuminate\Support\Facades\Auth;

/**
 * Class CommentService
 * @package App\Services\Ideas
 */
class CommentService
{
    /**
     * @param Idea $idea
     * @param array $data
     * @return bool
     */
    public function store(Idea $idea, array $data): bool
    {
        $data['user_id'] = Auth::user()->id;

        $idea->comments()->create($data);
    }

    /**
     * @param IdeaComment $comment
     * @param array $data
     * @return bool
     */
    public function update(IdeaComment $comment, array $data): bool
    {
        $comment->update($data);
        $comment->save();
    }
}