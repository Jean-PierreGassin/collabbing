<?php

namespace App\Policies;

use App\Models\IdeaApplication;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Class IdeaApplicationPolicy
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

    public function update(User $user, IdeaApplication $application): bool
    {
        if ($user->id === $application->idea->user_id) {
            return true;
        }

        return $user->id === $application->user_id;
    }

    public function delete(User $user, IdeaApplication $application): bool
    {
        return $user->id === $application->user_id;
    }

    public function manage(User $user, IdeaApplication $application): bool
    {
        return $user->id === $application->user_id;
    }
}
