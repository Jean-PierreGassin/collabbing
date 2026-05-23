<?php

namespace App\Policies;

use App\Models\Idea;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class IdeaPolicy
{
    use HandlesAuthorization;

    public function manage(User $user, Idea $ideaToEdit): bool
    {
        return $user->id === $ideaToEdit->user_id;
    }

    public function update(User $user, Idea $ideaToEdit): bool
    {
        return $user->id === $ideaToEdit->user_id;
    }

    public function createRepository(User $user, Idea $idea): bool
    {
        return $user->id === $idea->user_id;
    }

    public function inviteUsersToRepository(User $user, Idea $idea): bool
    {
        return $user->id === $idea->user_id;
    }

    public function createApplication(User $user, Idea $idea): bool
    {
        return $user->id !== $idea->user_id
            && ! $this->hasActiveApplicationFrom($user, $idea);
    }

    public function updateApplication(User $user, Idea $idea): bool
    {
        return $user->id === $idea->user_id;
    }

    public function deleteApplication(User $user, Idea $idea): bool
    {
        return $user->id === $idea->user_id;
    }

    public function storeApplication(User $user, Idea $idea): bool
    {
        return $user->id !== $idea->user_id
            && ! $this->hasActiveApplicationFrom($user, $idea);
    }

    public function storeSupporter(User $user, Idea $idea): bool
    {
        return $user->id !== $idea->user_id;
    }

    public function storeComment(User $user, Idea $idea): bool
    {
        return true;
    }

    private function hasActiveApplicationFrom(User $user, Idea $idea): bool
    {
        return $idea->applications()
            ->where('user_id', $user->id)
            ->whereIn('status', ['pending', 'approved'])
            ->exists();
    }
}
