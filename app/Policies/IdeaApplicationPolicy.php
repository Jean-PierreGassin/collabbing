<?php

namespace App\Policies;

use App\User;
use App\IdeaApplication;
use Illuminate\Auth\Access\HandlesAuthorization;

class IdeaApplicationPolicy
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


    public function update(User $user, IdeaApplication $application)
    {
        if ($user->id === $application->idea->user_id) {
            return true;
        }

        return ($user->id === $application->user_id);
    }

    public function delete(User $user, IdeaApplication $application)
    {
        return ($user->id === $application->user_id);
    }

    public function manage(User $user, IdeaApplication $application)
    {
        return ($user->id === $application->user_id);
    }
}
