<?php

namespace Tests\Browser;

use App\Idea;
use Tests\DuskTestCase;

class AccessTest extends DuskTestCase
{
    protected $idea;

    public function testThatAGuestCannotSeeTheApplyButtonOnAnIdea(): void
    {
        $this->idea = Idea::inRandomOrder()->first();

        $this->browse(
            function ($browser) {
                $browser->visit('/ideas/' . $this->idea->id)
                    ->assertDontSee('Apply to Collaborate');
            }
        );
    }

    public function testThatAGuestCannotSeeTheSupportButtonOnAnIdea(): void
    {
        $this->browse(
            function ($browser) {
                $browser->visit('/ideas/' . $this->idea->id)
                    ->assertDontSee('Support Idea 👍');
            }
        );
    }

    public function testThatAGuestCannotSeeTheCommentCreationBoxOnAnIdea(): void
    {
        $this->browse(
            function ($browser) {
                $browser->visit('/ideas/' . $this->idea->id)
                    ->assertDontSee('Submit Comment');
            }
        );
    }

    public function testThatAUserCannotSeeTheApplyButtonOnTheirOwnIdea(): void
    {
        $user = $this->idea->user;

        $this->browse(
            function ($first) use ($user) {
                $first->loginAs($user);
            }
        );

        $this->browse(
            function ($browser) {
                $browser->visit('/ideas/' . $this->idea->id)
                    ->assertDontSee('Apply to Collaborate');
            }
        );
    }

    public function testThatAUserCannotSeeTheApplyButtonOnAClosedIdea(): void
    {
        $this->browse(
            function ($browser) {
                $browser->visit('/ideas/' . $this->idea->id)
                    ->assertDontSee('Apply to Collaborate');
            }
        );
    }

    public function testThatAUserCannotSeeTheSupportButtonOnAClosedIdea(): void
    {
        $this->browse(
            function ($browser) {
                $browser->visit('/ideas/' . $this->idea->id)
                    ->assertDontSee('Support Idea 👍');
            }
        );
    }

    public function testThatAUserCannotSeeTheCommentSubmitButtonOnAClosedIdea(): void
    {
        $this->browse(
            function ($browser) {
                $browser->visit('/ideas/' . $this->idea->id)
                    ->assertDontSee('Submit Comment');
            }
        );
    }
}