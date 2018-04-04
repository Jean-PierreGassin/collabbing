<?php

namespace App\Policies;

use App\User;
use App\IdeaComment;
use Illuminate\Auth\Access\HandlesAuthorization;

class IdeaCommentPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function update(User $user, IdeaComment $comment)
    {
        return ($user->id === $comment->user_id);
    }

    public function delete(User $user, IdeaComment $comment)
    {
        return ($user->id === $comment->user_id);
    }

    public function manage(User $user, IdeaComment $comment)
    {
        return ($user->id === $comment->user_id);
    }
}
