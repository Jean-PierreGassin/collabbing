<?php

namespace Tests\Browser;

use App\Idea;
use Tests\DuskTestCase;

class AccessTest extends DuskTestCase
{
    protected $idea;

    /**
     * @test
     */
    public function guest_cannot_see_apply_button_on_idea(): void
    {
        $this->idea = Idea::inRandomOrder()->first();

        $this->browse(
            function ($browser) {
                $browser->visit('/ideas/' . $this->idea->id)
                    ->assertDontSee('Apply to Collaborate');
            }
        );
    }

    /**
     * @test
     */
    public function guest_cannot_see_support_button_on_idea(): void
    {
        $this->browse(
            function ($browser) {
                $browser->visit('/ideas/' . $this->idea->id)
                    ->assertDontSee('Support Idea 👍');
            }
        );
    }

    /**
     * @test
     */
    public function guest_cannot_see_comment_creation_button_on_idea(): void
    {
        $this->browse(
            function ($browser) {
                $browser->visit('/ideas/' . $this->idea->id)
                    ->assertDontSee('Submit Comment');
            }
        );
    }

    /**
     * @test
     */
    public function user_cannot_see_apply_button_on_own_idea(): void
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

    /**
     * @test
     */
    public function user_cannot_see_apply_button_on_closed_idea(): void
    {
        $this->browse(
            function ($browser) {
                $browser->visit('/ideas/' . $this->idea->id)
                    ->assertDontSee('Apply to Collaborate');
            }
        );
    }

    /**
     * @test
     */
    public function user_cannot_see_support_button_on_closed_idea(): void
    {
        $this->browse(
            function ($browser) {
                $browser->visit('/ideas/' . $this->idea->id)
                    ->assertDontSee('Support Idea 👍');
            }
        );
    }

    /**
     * @test
     */
    public function user_cannot_see_submit_button_on_closed_idea(): void
    {
        $this->browse(
            function ($browser) {
                $browser->visit('/ideas/' . $this->idea->id)
                    ->assertDontSee('Submit Comment');
            }
        );
    }
}