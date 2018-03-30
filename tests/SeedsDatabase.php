<?php

namespace Tests;

trait SeedsDatabase
{
    public function createUserAndIdeas()
    {
        $this->user = factory(\App\User::class)->make();

        $this->user->ideas()->save(factory(\App\Idea::class, 3)->make());

        $this->idea = $this->user->ideas->first();
        $this->comment = $this->user->comments->first();
        $this->supporter = $this->idea->supporters->first();
        $this->applicant = $this->idea->applicants->first();
    }
}