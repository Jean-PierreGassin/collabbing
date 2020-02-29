<?php

namespace Tests\Browser\Feature;

use App\User;
use Tests\DuskTestCase;

class IdeaTest extends DuskTestCase
{
    public function testThatAUserCanCreateAnIdea()
    {
        $this->browse(
            function ($first) {
                $first->loginAs(User::inRandomOrder()->first());
            }
        );

        $this->browse(
            function ($browser) {
                $browser->visit('/ideas/create')
                    ->type('title', 'My Amazing Idea!')
                    ->type('description', 'It\'s about toasters.')
                    ->type('communication', 'Slack')
                    ->press('Create Idea 💡')
                    ->assertSee('Created');
            }
        );
    }

    public function testThatAUserCanCommentOnAnIdea()
    {
        $this->browse(
            function ($browser) {
                $browser->visit('/ideas/1')
                    ->type('comment', 'What a great idea!')
                    ->press('Submit Comment')
                    ->assertSee('Submitted');
            }
        );
    }

    public function testThatAUserCanSupportAnIdea()
    {
        $this->browse(
            function ($browser) {
                $browser->visit('/ideas/1')
                    ->press('Support Idea')
                    ->assertSee('Supported');
            }
        );
    }

    public function testThatAUserCanApplyToCollaborateOnAnIdea()
    {
        $this->browse(
            function ($browser) {
                $browser->visit('/ideas/1/apply')
                    ->type('description', 'I\'m super good at this, accept me!')
                    ->press('Submit Application')
                    ->assertSee('Submitted');
            }
        );
    }

    public function testThatAUserCanApproveIdeaApplications()
    {
        $this->browse(
            function ($browser) {
                $browser->visit('/ideas/4/manage')
                    ->press('Approve Application')
                    ->assertPathIs('/ideas/3/manage')
                    ->assertSee('Approved');
            }
        );
    }

    public function testThatAUserCanDeclineIdeaApplications()
    {
        $this->browse(
            function ($browser) {
                $browser->visit('/ideas/4/manage')
                    ->press('Decline Application')
                    ->assertPathIs('/ideas/3/manage')
                    ->assertSee('Declined');
            }
        );
    }

    public function testThatAUserCanCloseAnIdea()
    {
        $this->browse(
            function ($browser) {
                $browser->visit('/ideas/4/manage')
                    ->press('Close Idea')
                    ->assertPathIs('/ideas/3')
                    ->assertSee('Closed');
            }
        );
    }
}