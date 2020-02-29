<?php

namespace App\Policies;

use App\Idea;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Class IdeaPolicy
 * @package App\Policies
 */
class IdeaPolicy
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
     * @param Idea $ideaToEdit
     * @return bool
     */
    public function manage(User $user, Idea $ideaToEdit): bool
    {
        return ($user->id === $ideaToEdit->user_id);
    }

    /**
     * @param User $user
     * @param Idea $ideaToEdit
     * @return bool
     */
    public function update(User $user, Idea $ideaToEdit): bool
    {
        return ($user->id === $ideaToEdit->user_id);
    }

    /**
     * @param User $user
     * @param Idea $idea
     * @return bool
     */
    public function createRepository(User $user, Idea $idea): bool
    {
        return ($user->id === $idea->user_id);
    }

    /**
     * @param User $user
     * @param Idea $idea
     * @return bool
     */
    public function inviteUsersToRepository(User $user, Idea $idea): bool
    {
        return ($user->id === $idea->user_id);
    }

    /**
     * @param User $user
     * @param Idea $idea
     * @return bool
     */
    public function createApplication(User $user, Idea $idea): bool
    {
        return ($user->id !== $idea->user_id);
    }

    /**
     * @param User $user
     * @param Idea $idea
     * @return bool
     */
    public function updateApplication(User $user, Idea $idea): bool
    {
        return ($user->id === $idea->user_id);
    }

    /**
     * @param User $user
     * @param Idea $idea
     * @return bool
     */
    public function deleteApplication(User $user, Idea $idea): bool
    {
        return ($user->id === $idea->user_id);
    }

    /**
     * @param User $user
     * @param Idea $idea
     * @return bool
     */
    public function storeApplication(User $user, Idea $idea): bool
    {
        return ($user->id !== $idea->user_id);
    }

    /**
     * @param User $user
     * @param Idea $idea
     * @return bool
     */
    public function storeSupporter(User $user, Idea $idea): bool
    {
        return ($user->id !== $idea->user_id);
    }

    /**
     * @param User $user
     * @param Idea $idea
     * @return bool
     */
    public function storeComment(User $user, Idea $idea): bool
    {
        if ($idea->hasApplicationFromUser($user->id, 'approved')) {
            return true;
        }

        if ($idea->user_id === $user->id) {
            return true;
        }

        return false;
    }
}
