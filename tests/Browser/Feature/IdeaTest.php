<?php

namespace Tests\Browser\Feature;

use App\User;
use Tests\DuskTestCase;

class IdeaTest extends DuskTestCase
{
    public function testThatAUserCanCreateAnIdea(): void
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

    public function testThatAUserCanCommentOnAnIdea(): void
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

    public function testThatAUserCanSupportAnIdea(): void
    {
        $this->browse(
            function ($browser) {
                $browser->visit('/ideas/1')
                    ->press('Support Idea')
                    ->assertSee('Supported');
            }
        );
    }

    public function testThatAUserCanApplyToCollaborateOnAnIdea(): void
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

    public function testThatAUserCanApproveIdeaApplications(): void
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

    public function testThatAUserCanDeclineIdeaApplications(): void
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

    public function testThatAUserCanCloseAnIdea(): void
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