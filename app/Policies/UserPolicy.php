<?php

namespace App\Policies;

use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Class UserPolicy
 * @package App\Policies
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

    /**
     * @param User $user
     * @param User $userToEdit
     * @return bool
     */
    public function manage(User $user, User $userToEdit): bool
    {
        return (strtolower($user->username) === strtolower($userToEdit->username));
    }

    /**
     * @param User $user
     * @param User $userToEdit
     * @return bool
     */
    public function update(User $user, User $userToEdit): bool
    {
        return (strtolower($user->username) === strtolower($userToEdit->username));
    }
}
