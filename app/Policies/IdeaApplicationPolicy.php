<?php

namespace App\Policies;

use App\Models\Idea;
use App\Models\IdeaApplication;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class IdeaApplicationPolicy
{
    use HandlesAuthorization;

    public function viewThread(User $user, IdeaApplication $application): bool
    {
        return $this->isThreadParticipant($user, $application);
    }

    public function message(User $user, IdeaApplication $application): bool
    {
        return $application->isPending() && $this->isThreadParticipant($user, $application);
    }

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

    private function isThreadParticipant(User $user, IdeaApplication $application): bool
    {
        if ((int) $user->id === (int) $application->user_id) {
            return true;
        }

        $idea = $application->idea;

        return $idea instanceof Idea && (int) $user->id === (int) $idea->user_id;
    }
}
