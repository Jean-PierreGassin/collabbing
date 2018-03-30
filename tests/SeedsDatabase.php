<?php

namespace Tests;

trait SeedsDatabase
{
    public function createUserAndIdeas()
    {
        $this->user = factory(\App\User::class)
            ->create()
            ->each(function ($user) {
                $user->ideas()->save(
                    factory(\App\Idea::class, 2)->create(['user_id' => $user->id])->make()
                );
            });

        $this->idea = $this->user->ideas->first();
        $this->comment = $this->user->comments->first();
        $this->supporter = $this->idea->supporters->first();
        $this->applicant = $this->idea->applicants->first();
    }
}