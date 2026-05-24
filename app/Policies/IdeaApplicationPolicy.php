<?php

namespace App\Policies;

use App\Models\Idea;
use App\Models\IdeaApplication;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class IdeaApplicationPolicy
{
    use HandlesAuthorization;

    public function update(User $user, IdeaApplication $application): bool
    {
        return $application->isPending() && (int) $user->id === (int) $application->user_id;
    }

    public function delete(User $user, IdeaApplication $application): bool
    {
        $idea = $application->idea;

        if ($idea instanceof Idea && (int) $user->id === (int) $idea->user_id) {
            return true;
        }

        return $application->isPending() && (int) $user->id === (int) $application->user_id;
    }

    public function manage(User $user, IdeaApplication $application): bool
    {
        return (int) $user->id === (int) $application->user_id;
    }
}
