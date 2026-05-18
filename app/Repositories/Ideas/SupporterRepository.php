<?php

namespace App\Repositories\Ideas;

use App\Models\Idea;
use App\Models\IdeaSupporter;
use App\Models\User;

class SupporterRepository
{
    public function create(Idea $idea, User $user): IdeaSupporter
    {
        return IdeaSupporter::query()->firstOrCreate([
            'user_id' => $user->id,
            'idea_id' => $idea->id,
        ]);
    }

    public function destroy(IdeaSupporter $supporter): bool
    {
        return $supporter->delete();
    }

    public function getSupportFromUser(Idea $idea, User $user): ?IdeaSupporter
    {
        $supporter = $idea->supporters()->where('user_id', $user->id)->first();

        if (! $supporter instanceof IdeaSupporter) {
            return null;
        }

        return $supporter;
    }
}
