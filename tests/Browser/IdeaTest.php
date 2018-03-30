<?php

namespace Tests\Browser;

use Tests\DuskTestCase;
use Tests\SeedsDatabase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class IdeaTest extends DuskTestCase
{
    use RefreshDatabase, SeedsDatabase;

    public function testThatAUserCanSeeAListOfIdeas()
    {
        $this->browse(function ($first) {
            $this->createUserWithClosedIdea();

            $first->loginAs(\App\User::find(1));
        });

        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->assertSee('Ideas');
        });
    }

    public function testThatAUserCanSeeHighlightedIdeas()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->assertSee('Trending Ideas');
        });
    }

    public function testThatAUserCanSeeAnIdeasComments()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/ideas/1')
                ->assertSee('Comments');
        });
    }

    public function testThatAUserCanSeeAnIdeasSupporters()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/ideas/1')
                ->assertSee('Supporters');
        });
    }

    public function testThatAUserCanSeeAnIdeasCollaborators()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/ideas/1')
                ->assertSee('Collaborators');
        });
    }

    public function testThatAUserCanSeeTheirIdeasPendingApplicants()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/ideas/1/manage')
                ->assertSee('Pending Applicants');
        });
    }

    public function testThatAUserCanSeeTheirIdeasApprovedApplicants()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/ideas/1/manage')
                ->assertSee('Approved Applicants');
        });
    }

    public function testThatAUserCanSeeTheirIdeasDeclinedApplicants()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/ideas/1/manage')
                ->assertSee('Declined Applicants');
        });
    }
}