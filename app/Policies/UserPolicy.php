<?php

namespace App\Policies;

use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
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

    public function manage(User $user, User $userToEdit)
    {
        return (strtolower($user->username) === strtolower($userToEdit->username));
    }

    public function update(User $user, User $userToEdit)
    {
        return (strtolower($user->username) === strtolower($userToEdit->username));
    }
}
