<?php

namespace Tests\Browser\Traits;

trait SeedsDatabase
{
    public function createUsersWithClosedIdea()
    {
        $users = factory(\App\User::class, 2)
            ->create()
            ->each(function ($user) {
                $user->ideas()->save(
                    factory(\App\Idea::class, 2)->create(['user_id' => $user->id])->make()
                );
            });

        foreach ($users as $user) {
            $closedIdea = $user->ideas()->first();
            $closedIdea->update(['status' => 'closed']);
        }
    }
}

