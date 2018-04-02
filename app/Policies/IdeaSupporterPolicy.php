<?php

namespace App\Policies;

use App\User;
use App\IdeaSupporter;
use Illuminate\Auth\Access\HandlesAuthorization;

class IdeaSupporterPolicy
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

    public function delete(User $user, IdeaSupporter $supportToEdit)
    {
        return ($user->id === $supportToEdit->user_id);
    }
}
