<?php

namespace Tests\Browser;

use App\Idea;
use App\User;
use Tests\DuskTestCase;

class IdeaTest extends DuskTestCase
{
    protected $idea;

    public function testThatAUserCanSeeAListOfIdeas(): void
    {
        $this->browse(
            function ($first) {
                $user = User::inRandomOrder()->first();

                $first->loginAs($user);
            }
        );

        $this->browse(
            function ($browser) {
                $browser->visit('/')
                    ->assertSee('Ideas');
            }
        );
    }

    public function testThatAUserCanSeeHighlightedIdeas(): void
    {
        $this->browse(
            function ($browser) {
                $browser->visit('/')
                    ->assertSee('Trending Ideas');
            }
        );
    }

    public function testThatAUserCanSeeAnIdeasComments(): void
    {
        $this->idea = Idea::inRandomOrder()->first();

        $this->browse(
            function ($browser) {
                $browser->visit('/ideas/' . $this->idea->id)
                    ->assertSee('Comments');
            }
        );
    }

    public function testThatAUserCanSeeAnIdeasSupporters(): void
    {
        $this->browse(
            function ($browser) {
                $browser->visit('/ideas/' . $this->idea->id)
                    ->assertSee('Supporters');
            }
        );
    }

    public function testThatAUserCanSeeAnIdeasCollaborators(): void
    {
        $this->browse(
            function ($browser) {
                $browser->visit('/ideas/' . $this->idea->id)
                    ->assertSee('Collaborators');
            }
        );
    }

    public function testThatAUserCanSeeTheirIdeasPendingApplications(): void
    {
        $this->browse(
            function ($browser) {
                $browser->visit('/ideas/' . $this->idea->id . '/manage')
                    ->assertSee('Pending Applications');
            }
        );
    }

    public function testThatAUserCanSeeTheirIdeasApprovedApplications(): void
    {
        $this->browse(
            function ($browser) {
                $browser->visit('/ideas/' . $this->idea->id . '/manage')
                    ->assertSee('Approved Applications');
            }
        );
    }

    public function testThatAUserCanSeeTheirIdeasDeclinedApplications(): void
    {
        $this->browse(
            function ($browser) {
                $browser->visit('/ideas/' . $this->idea->id . '/manage')
                    ->assertSee('Declined Applications');
            }
        );
    }
}