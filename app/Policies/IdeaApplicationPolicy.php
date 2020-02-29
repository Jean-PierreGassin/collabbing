<?php

namespace App\Policies;

use App\Models\IdeaApplication;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Class IdeaApplicationPolicy
 * @package App\Policies
 */
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


    /**
     * @param User $user
     * @param IdeaApplication $application
     * @return bool
     */
    public function update(User $user, IdeaApplication $application): bool
    {
        if ($user->id === $application->idea->user_id) {
            return true;
        }

        return ($user->id === $application->user_id);
    }

    /**
     * @param User $user
     * @param IdeaApplication $application
     * @return bool
     */
    public function delete(User $user, IdeaApplication $application): bool
    {
        return ($user->id === $application->user_id);
    }

    /**
     * @param User $user
     * @param IdeaApplication $application
     * @return bool
     */
    public function manage(User $user, IdeaApplication $application): bool
    {
        return ($user->id === $application->user_id);
    }
}
