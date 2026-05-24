<?php

namespace App\Repositories\Ideas;

use App\Data\Ideas\IdeaCommentData;
use App\Models\Idea;
use App\Models\IdeaComment;
use App\Models\User;
use Illuminate\Support\Collection;

class CommentRepository
{
    public function create(Idea $idea, User $user, IdeaCommentData $data): IdeaComment
    {
        return IdeaComment::query()->create([
            ...$data->createAttributes(),
            'idea_id' => $idea->id,
            'user_id' => $user->id,
        ]);
    }

    public function update(IdeaComment $comment, IdeaCommentData $data): bool
    {
        $comment->update($data->updateAttributes());

        return $comment->save();
    }

    public function repliesFor(IdeaComment $comment): Collection
    {
        return $comment->replies()
            ->with('user')
            ->get();
    }

    public function mentionedUsers(array $usernames): Collection
    {
        if ($usernames === []) {
            return collect();
        }

        return User::query()
            ->whereIn('username', array_unique($usernames))
            ->get()
            ->keyBy('username');
    }
}
