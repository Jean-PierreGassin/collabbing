<?php

namespace App\Policies;

use App\User;
use App\Idea;
use Illuminate\Auth\Access\HandlesAuthorization;

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

    public function manage(User $user, Idea $ideaToEdit)
    {
        return ($user->id === $ideaToEdit->user_id);
    }

    public function update(User $user, Idea $ideaToEdit)
    {
        return ($user->id === $ideaToEdit->user_id);
    }

    public function storeSupporter(User $user, Idea $idea)
    {
        return ($user->id !== $idea->user_id);
    }

    public function createApplication(User $user, Idea $idea)
    {
        return ($user->id !== $idea->user_id);
    }

    public function updateApplication(User $user, Idea $idea)
    {
        return ($user->id === $idea->user_id);
    }

    public function deleteApplication(User $user, Idea $idea)
    {
        return ($user->id === $idea->user_id);
    }

    public function storeApplication(User $user, Idea $idea)
    {
        return ($user->id !== $idea->user_id);
    }
}
