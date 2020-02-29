<?php

namespace App\Policies;

use App\Models\IdeaComment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Class IdeaCommentPolicy
 * @package App\Policies
 */
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

    /**
     * @param User $user
     * @param IdeaComment $comment
     * @return bool
     */
    public function update(User $user, IdeaComment $comment): bool
    {
        return ($user->id === $comment->user_id);
    }

    /**
     * @param User $user
     * @param IdeaComment $comment
     * @return bool
     */
    public function delete(User $user, IdeaComment $comment): bool
    {
        return ($user->id === $comment->user_id);
    }

    /**
     * @param User $user
     * @param IdeaComment $comment
     * @return bool
     */
    public function manage(User $user, IdeaComment $comment): bool
    {
        return ($user->id === $comment->user_id);
    }
}
