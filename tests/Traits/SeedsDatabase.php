<?php

namespace Tests\Traits;

trait SeedsDatabase
{
    public function createUsersWithClosedIdea()
    {
        factory(\App\User::class, 2)
            ->create()
            ->each(function ($user) {
                $user->ideas()->save(factory(\App\Idea::class)->make());
            });

        $this->createIdeaRelations(\App\User::find(1));
        $this->createIdeaRelations(\App\User::find(2));
    }

    protected function createIdeaRelations($user)
    {
        foreach ($user->ideas->all() as $idea) {
            $idea->comments()
                ->save(factory(\App\IdeaComment::class)->make(['user_id' => $user->id]));
            $idea->supporters()
                ->save(factory(\App\IdeaSupporter::class)->make(['user_id' => $user->id]));
            $idea->applications()
                ->save(factory(\App\IdeaApplication::class)->make(['user_id' => $user->id]));
        }

        // 'close' the first idea for each user
        $idea = $user->ideas->first();

        $idea->status = 'closed';
        $idea->save();
    }
}

