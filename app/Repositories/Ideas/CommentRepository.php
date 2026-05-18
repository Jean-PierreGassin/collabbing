<?php

namespace App\Repositories\Ideas;

use App\Models\Idea;
use App\Models\IdeaComment;
use App\Models\User;

class CommentRepository
{
    public function create(Idea $idea, User $user, array $data): IdeaComment
    {
        return IdeaComment::query()->create([
            ...$data,
            'idea_id' => $idea->id,
            'user_id' => $user->id,
        ]);
    }

    public function update(IdeaComment $comment, array $data): bool
    {
        $comment->update($data);

        return $comment->save();
    }
}
