<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Class UserPolicy
 */
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

    public function manage(User $user, User $userToEdit): bool
    {
        return strtolower($user->username) === strtolower($userToEdit->username);
    }

    public function update(User $user, User $userToEdit): bool
    {
        return strtolower($user->username) === strtolower($userToEdit->username);
    }
}
