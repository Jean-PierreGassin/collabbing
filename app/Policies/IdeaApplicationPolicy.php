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

    public function manage(User $user, IdeaApplication $applicationToEdit)
    {
        return ($user->id === $applicationToEdit->user_id);
    }

    public function update(User $user, IdeaApplication $applicationToEdit)
    {
        return ($user->id === $applicationToEdit->user_id);
    }
}
