<?php

namespace App\Repositories\Ideas;

use App\Models\Idea;
use App\Models\IdeaComment;
use App\Models\User;

class CommentRepository
{
    public function create(Idea $idea, User $user, array $data): IdeaComment
    {
        $data['user_id'] = $user->id;

        return $idea->comments()->create($data);
    }

    public function update(IdeaComment $comment, array $data): bool
    {
        $comment->update($data);

        return $comment->save();
    }
}
