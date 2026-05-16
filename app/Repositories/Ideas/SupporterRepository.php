<?php

namespace App\Repositories\Ideas;

use App\Models\Idea;
use App\Models\IdeaSupporter;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class SupporterRepository
{
    public function create(Idea $idea, User $user): Model
    {
        return $idea->supporters()->firstOrCreate([
            'user_id' => $user->id,
            'idea_id' => $idea->id,
        ]);
    }

    public function destroy(IdeaSupporter $supporter): bool
    {
        return $supporter->delete();
    }

    public function getSupportFromUser(Idea $idea, User $user): ?Model
    {
        return $idea->supporters()->where('user_id', $user->id)->first();
    }
}
