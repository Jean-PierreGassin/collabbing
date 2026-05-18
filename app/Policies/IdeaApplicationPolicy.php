<?php

namespace App\Policies;

use App\Models\Idea;
use App\Models\IdeaApplication;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class IdeaApplicationPolicy
{
    use HandlesAuthorization;

    public function __construct()
    {
        //
    }

    public function update(User $user, IdeaApplication $application): bool
    {
        $idea = $application->idea;

        if ($idea instanceof Idea && $user->id === $idea->user_id) {
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
