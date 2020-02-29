<?php

namespace Tests\Browser;

use App\Models\Idea;
use App\Models\User;
use Tests\DuskTestCase;

class IdeaTest extends DuskTestCase
{
    protected $idea;

    /**
     * @test
     */
    public function user_can_see_list_of_ideas(): void
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

    /**
     * @test
     */
    public function user_can_see_highlighted_ideas(): void
    {
        $this->browse(
            function ($browser) {
                $browser->visit('/')
                    ->assertSee('Trending Ideas');
            }
        );
    }

    /**
     * @test
     */
    public function user_can_see_idea_comments(): void
    {
        $this->idea = Idea::inRandomOrder()->first();

        $this->browse(
            function ($browser) {
                $browser->visit('/ideas/' . $this->idea->id)
                    ->assertSee('Comments');
            }
        );
    }

    /**
     * @test
     */
    public function user_can_see_idea_supporters(): void
    {
        $this->browse(
            function ($browser) {
                $browser->visit('/ideas/' . $this->idea->id)
                    ->assertSee('Supporters');
            }
        );
    }

    /**
     * @test
     */
    public function user_can_see_idea_collaborators(): void
    {
        $this->browse(
            function ($browser) {
                $browser->visit('/ideas/' . $this->idea->id)
                    ->assertSee('Collaborators');
            }
        );
    }

    /**
     * @test
     */
    public function user_can_see_their_ideas_pending_applications(): void
    {
        $this->browse(
            function ($browser) {
                $browser->visit('/ideas/' . $this->idea->id . '/manage')
                    ->assertSee('Pending Applications');
            }
        );
    }

    /**
     * @test
     */
    public function user_can_see_their_ideas_approved_applications(): void
    {
        $this->browse(
            function ($browser) {
                $browser->visit('/ideas/' . $this->idea->id . '/manage')
                    ->assertSee('Approved Applications');
            }
        );
    }

    /**
     * @test
     */
    public function user_can_see_their_ideas_declined_applications(): void
    {
        $this->browse(
            function ($browser) {
                $browser->visit('/ideas/' . $this->idea->id . '/manage')
                    ->assertSee('Declined Applications');
            }
        );
    }
}