<?php

namespace Tests\Browser;

use App\Models\User;
use Tests\DuskTestCase;

class UserTest extends DuskTestCase
{
    protected $user;

    /**
     * @test
     */
    public function user_can_see_their_dashboard(): void
    {
        $this->browse(
            function ($first) {
                $this->user = User::inRandomOrder()->first();

                $first->loginAs($this->user);
            }
        );

        $this->browse(
            function ($browser) {
                $browser->visit('/dashboard')
                    ->assertSee('Dashboard');
            }
        );
    }

    /**
     * @test
     */
    public function user_can_see_their_profile(): void
    {
        $this->browse(
            function ($browser) {
                $browser->visit('/users/me')
                    ->assertSee('Your Profile');
            }
        );
    }

    /**
     * @test
     */
    public function user_can_see_another_users_profile(): void
    {
        $this->browse(
            function ($browser) {
                $browser->visit('/users/john.smith')
                    ->assertSee($this->user . '\'s Profile');
            }
        );
    }
}