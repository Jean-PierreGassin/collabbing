<?php

namespace Tests\Browser;

use Tests\DuskTestCase;

class IdeaTest extends DuskTestCase
{
    protected $idea;

    public function testThatAUserCanSeeAListOfIdeas()
    {
        $this->browse(function ($first) {
            $user = \App\User::inRandomOrder()->first();

            $first->loginAs($user);
        });

        $this->browse(function ($browser) {
            $browser->visit('/')
                ->assertSee('Ideas');
        });
    }

    public function testThatAUserCanSeeHighlightedIdeas()
    {
        $this->browse(function ($browser) {
            $browser->visit('/')
                ->assertSee('Trending Ideas');
        });
    }

    public function testThatAUserCanSeeAnIdeasComments()
    {
        $this->idea = \App\Idea::inRandomOrder()->first();

        $this->browse(function ($browser) {
            $browser->visit('/ideas/' . $this->idea->id)
                ->assertSee('Comments');
        });
    }

    public function testThatAUserCanSeeAnIdeasSupporters()
    {
        $this->browse(function ($browser) {
            $browser->visit('/ideas/' . $this->idea->id)
                ->assertSee('Supporters');
        });
    }

    public function testThatAUserCanSeeAnIdeasCollaborators()
    {
        $this->browse(function ($browser) {
            $browser->visit('/ideas/' . $this->idea->id)
                ->assertSee('Collaborators');
        });
    }

    public function testThatAUserCanSeeTheirIdeasPendingApplications()
    {
        $this->browse(function ($browser) {
            $browser->visit('/ideas/' . $this->idea->id . '/manage')
                ->assertSee('Pending Applications');
        });
    }

    public function testThatAUserCanSeeTheirIdeasApprovedApplications()
    {
        $this->browse(function ($browser) {
            $browser->visit('/ideas/' . $this->idea->id . '/manage')
                ->assertSee('Approved Applications');
        });
    }

    public function testThatAUserCanSeeTheirIdeasDeclinedApplications()
    {
        $this->browse(function ($browser) {
            $browser->visit('/ideas/' . $this->idea->id . '/manage')
                ->assertSee('Declined Applications');
        });
    }
}