<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function __construct()
    {
        //
    }

    public function manage(User $user, User $userToEdit): bool
    {
        return strtolower($user->username) === strtolower($userToEdit->username);
    }

    public function update(User $user, User $userToEdit): bool
    {
        return strtolower($user->username) === strtolower($userToEdit->username);
    }
}
