<?php

namespace App\Services\Ideas;

use App\Models\Idea;
use App\Models\IdeaComment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Class CommentService
 */
class CommentService
{
    /**
     * @return IdeaComment
     */
    public function store(Idea $idea, array $data): Model
    {
        $data['user_id'] = Auth::user()->id;

        return $idea->comments()->create($data);
    }

    public function update(IdeaComment $comment, array $data): bool
    {
        $comment->update($data);

        return $comment->save();
    }
}
