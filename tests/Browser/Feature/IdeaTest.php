<?php

namespace Tests\Browser\Feature;

use App\Models\User;
use Tests\DuskTestCase;

class IdeaTest extends DuskTestCase
{
    /**
     * @test
     */
    public function user_can_create_idea(): void
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

    /**
     * @test
     */
    public function user_can_comment_on_idea(): void
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

    /**
     * @test
     */
    public function user_can_support_idea(): void
    {
        $this->browse(
            function ($browser) {
                $browser->visit('/ideas/1')
                    ->press('Support Idea')
                    ->assertSee('Supported');
            }
        );
    }

    /**
     * @test
     */
    public function user_can_apply_to_collaborate_on_idea(): void
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

    /**
     * @test
     */
    public function user_can_approve_idea_application(): void
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

    /**
     * @test
     */
    public function user_can_decline_idea_application(): void
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

    /**
     * @test
     */
    public function user_can_close_idea(): void
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