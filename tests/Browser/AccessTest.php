<?php

namespace Tests\Browser;

use Tests\DuskTestCase;

class AccessTest extends DuskTestCase
{
    protected $idea;

    public function testThatAGuestCannotSeeTheApplyButtonOnAnIdea()
    {
        $this->idea = \App\Idea::inRandomOrder()->first();

        $this->browse(function ($browser) {
            $browser->visit('/ideas/' . $this->idea->id)
                ->assertDontSee('Apply to Collaborate');
        });
    }

    public function testThatAGuestCannotSeeTheSupportButtonOnAnIdea()
    {
        $this->browse(function ($browser) {
            $browser->visit('/ideas/' . $this->idea->id)
                ->assertDontSee('Support Idea 👍');
        });
    }

    public function testThatAGuestCannotSeeTheCommentCreationBoxOnAnIdea()
    {
        $this->browse(function ($browser) {
            $browser->visit('/ideas/' . $this->idea->id)
                ->assertDontSee('Submit Comment');
        });
    }

    public function testThatAUserCannotSeeTheApplyButtonOnTheirOwnIdea()
    {
        $user = $this->idea->user;

        $this->browse(function ($first) use ($user) {
            $first->loginAs($user);
        });

        $this->browse(function ($browser) {
            $browser->visit('/ideas/' . $this->idea->id)
                ->assertDontSee('Apply to Collaborate');
        });
    }

    public function testThatAUserCannotSeeTheApplyButtonOnAClosedIdea()
    {
        $this->browse(function ($browser) {
            $browser->visit('/ideas/' . $this->idea->id)
                ->assertDontSee('Apply to Collaborate');
        });
    }

    public function testThatAUserCannotSeeTheSupportButtonOnAClosedIdea()
    {
        $this->browse(function ($browser) {
            $browser->visit('/ideas/' . $this->idea->id)
                ->assertDontSee('Support Idea 👍');
        });
    }

    public function testThatAUserCannotSeeTheCommentSubmitButtonOnAClosedIdea()
    {
        $this->browse(function ($browser) {
            $browser->visit('/ideas/' . $this->idea->id)
                ->assertDontSee('Submit Comment');
        });
    }
}