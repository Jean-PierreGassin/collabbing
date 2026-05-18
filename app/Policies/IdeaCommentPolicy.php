<?php

namespace App\Policies;

use App\Models\IdeaComment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class IdeaCommentPolicy
{
    use HandlesAuthorization;

    public function __construct()
    {
        //
    }

    public function update(User $user, IdeaComment $comment): bool
    {
        return $user->id === $comment->user_id;
    }

    public function delete(User $user, IdeaComment $comment): bool
    {
        return $user->id === $comment->user_id;
    }

    public function manage(User $user, IdeaComment $comment): bool
    {
        return $user->id === $comment->user_id;
    }
}
