<?php

namespace Tests\Browser;

use Tests\DuskTestCase;
use Tests\Browser\Traits\SeedsDatabase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AccessTest extends DuskTestCase
{
    use RefreshDatabase, SeedsDatabase;

    public function testThatAGuestCannotSeeTheApplyButtonOnAnIdea()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/ideas/1')
                ->assertDontSee('Apply to Collaborate');
        });
    }

    public function testThatAGuestCannotSeeTheSupportButtonOnAClosedIdea()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/ideas/1')
                ->assertDontSee('Support Idea 👍');
        });
    }

    public function testThatAGuestCannotSeeTheCommentCreationBoxOnAClosedIdea()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/ideas/1')
                ->assertDontSee('Submit Comment');
        });
    }

    public function testThatAUserCannotSeeTheApplyButtonOnTheirOwnIdea()
    {
        $this->browse(function ($first) {
            $this->createUserWithClosedIdea();

            $first->loginAs(\App\User::find(1));
        });

        $this->browse(function (Browser $browser) {
            $browser->visit('/ideas/1')
                ->assertDontSee('Apply to Collaborate');
        });
    }

    public function testThatAUserCannotSeeTheApplyButtonOnAClosedIdea()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/ideas/1')
                ->assertDontSee('Apply to Collaborate');
        });
    }

    public function testThatAUserCannotSeeTheSupportButtonOnAClosedIdea()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/ideas/1')
                ->assertDontSee('Support Idea 👍');
        });
    }

    public function testThatAUserCannotSeeTheCommentSubmitButtonOnAClosedIdea()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/ideas/1')
                ->assertDontSee('Submit Comment');
        });
    }
}