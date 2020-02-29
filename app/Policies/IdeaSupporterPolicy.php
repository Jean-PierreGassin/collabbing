<?php

namespace App\Policies;

use App\Models\IdeaSupporter;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Class IdeaSupporterPolicy
 * @package App\Policies
 */
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

    /**
     * @param User $user
     * @param IdeaSupporter $supportToEdit
     * @return bool
     */
    public function delete(User $user, IdeaSupporter $supportToEdit): bool
    {
        return ($user->id === $supportToEdit->user_id);
    }
}
