<?php

namespace Tests\Traits;

use App\Models\Idea;
use App\Models\IdeaApplication;
use App\Models\IdeaComment;
use App\Models\IdeaSupporter;
use App\Models\User;

trait SeedsDatabase
{
    public function createUsersWithClosedIdea(): void
    {
        User::factory()
            ->count(2)
            ->create()
            ->each(
                function (User $user) {
                    $user->ideas()->save(Idea::factory()->make());
                }
            );

        $this->createIdeaRelations(User::find(1));
        $this->createIdeaRelations(User::find(2));
    }

    protected function createIdeaRelations($user): void
    {
        foreach ($user->ideas->all() as $idea) {
            $idea->comments()
                ->save(IdeaComment::factory()->make(['user_id' => $user->id]));
            $idea->supporters()
                ->save(IdeaSupporter::factory()->make(['user_id' => $user->id]));
            $idea->applications()
                ->save(IdeaApplication::factory()->make(['user_id' => $user->id]));
        }

        // 'close' the first idea for each user
        $idea = $user->ideas->first();

        $idea->status = 'closed';
        $idea->save();
    }
}
