<?php

namespace Tests\Browser\Traits;

trait SeedsDatabase
{
    public function createUserWithClosedIdea()
    {
        $users = factory(\App\User::class, 2)->create();

        foreach ($users as $user) {
            $user->ideas()->save(factory(\App\Idea::class, 2)->create());

            $closedIdea = $user->ideas()->first();
            $closedIdea->update(['status' => 'closed']);
        }
    }
}

