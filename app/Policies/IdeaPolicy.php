<?php

namespace App\Policies;

use App\Models\Idea;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Class IdeaPolicy
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

    public function manage(User $user, Idea $ideaToEdit): bool
    {
        return $user->id === $ideaToEdit->user_id;
    }

    public function update(User $user, Idea $ideaToEdit): bool
    {
        return $user->id === $ideaToEdit->user_id;
    }

    public function createRepository(User $user, Idea $idea): bool
    {
        return $user->id === $idea->user_id;
    }

    public function inviteUsersToRepository(User $user, Idea $idea): bool
    {
        return $user->id === $idea->user_id;
    }

    public function createApplication(User $user, Idea $idea): bool
    {
        return $user->id !== $idea->user_id;
    }

    public function updateApplication(User $user, Idea $idea): bool
    {
        return $user->id === $idea->user_id;
    }

    public function deleteApplication(User $user, Idea $idea): bool
    {
        return $user->id === $idea->user_id;
    }

    public function storeApplication(User $user, Idea $idea): bool
    {
        return $user->id !== $idea->user_id;
    }

    public function storeSupporter(User $user, Idea $idea): bool
    {
        return $user->id !== $idea->user_id;
    }

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
