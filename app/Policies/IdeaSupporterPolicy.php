<?php

namespace App\Policies;

use App\Models\IdeaSupporter;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class IdeaSupporterPolicy
{
    use HandlesAuthorization;

    public function __construct()
    {
        //
    }

    public function delete(User $user, IdeaSupporter $supportToEdit): bool
    {
        return $user->id === $supportToEdit->user_id;
    }
}
