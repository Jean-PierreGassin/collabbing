<?php

namespace Tests\Traits;

use App\Idea;
use App\IdeaApplication;
use App\IdeaComment;
use App\IdeaSupporter;
use App\User;

trait SeedsDatabase
{
    public function createUsersWithClosedIdea()
    {
        factory(User::class, 2)
            ->create()
            ->each(
                function ($user) {
                    $user->ideas()->save(factory(Idea::class)->make());
                }
            );

        $this->createIdeaRelations(User::find(1));
        $this->createIdeaRelations(User::find(2));
    }

    protected function createIdeaRelations($user)
    {
        foreach ($user->ideas->all() as $idea) {
            $idea->comments()
                ->save(factory(IdeaComment::class)->make(['user_id' => $user->id]));
            $idea->supporters()
                ->save(factory(IdeaSupporter::class)->make(['user_id' => $user->id]));
            $idea->applications()
                ->save(factory(IdeaApplication::class)->make(['user_id' => $user->id]));
        }

        // 'close' the first idea for each user
        $idea = $user->ideas->first();

        $idea->status = 'closed';
        $idea->save();
    }
}

