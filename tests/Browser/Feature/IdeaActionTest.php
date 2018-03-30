<?php

namespace Tests\Browser\Feature;

use Tests\DuskTestCase;
use Tests\Browser\Traits\SeedsDatabase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class IdeaActionTest extends DuskTestCase
{
    use RefreshDatabase, SeedsDatabase;

    public function testThatAUserCanCreateAnIdea()
    {
        $this->browse(function ($first) {
            $this->createUserWithClosedIdea();

            $first->loginAs(User::find(2));
        });

        $this->browse(function (Browser $browser) {
            $browser->visit('/ideas/create')
                ->type('title', 'My Amazing Idea!')
                ->type('description', 'It\'s about toasters.')
                ->type('communication', 'Slack')
                ->press('Create Idea 💡')
                ->assertSee('Created');
        });
    }

    public function testThatAUserCanCommentOnAnIdea()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/ideas/1')
                ->type('comment', 'What a great idea!')
                ->press('Submit Comment')
                ->assertSee('Submitted');
        });
    }

    public function testThatAUserCanSupportAnIdea()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/ideas/1')
                ->press('Support Idea')
                ->assertSee('Supported');
        });
    }

    public function testThatAUserCanApplyToCollaborateOnAnIdea()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/ideas/1/apply')
                ->type('description', 'I\'m super good at this, accept me!')
                ->press('Submit Application')
                ->assertSee('Submitted');
        });
    }

    public function testThatAUserCanApproveIdeaApplicants()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/ideas/4/manage')
                ->press('Approve Applicant')
                ->assertPathIs('/ideas/3/manage')
                ->assertSee('Approved');
        });
    }

    public function testThatAUserCanDeclineIdeaApplicants()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/ideas/4/manage')
                ->press('Decline Applicant')
                ->assertPathIs('/ideas/3/manage')
                ->assertSee('Declined');
        });
    }

    public function testThatAUserCanCloseAnIdea()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/ideas/4/manage')
                ->press('Close Idea')
                ->assertPathIs('/ideas/3')
                ->assertSee('Closed');
        });
    }
}