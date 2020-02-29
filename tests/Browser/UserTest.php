<?php

namespace Tests\Browser;

use App\User;
use Tests\DuskTestCase;

class UserTest extends DuskTestCase
{
    protected $user;

    public function testThatAUserCanSeeTheirDashboard()
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

    public function testThatAUserCanSeeTheirProfile()
    {
        $this->browse(
            function ($browser) {
                $browser->visit('/users/me')
                    ->assertSee('Your Profile');
            }
        );
    }

    public function testThatAUserCanSeeAnotherUsersProfile()
    {
        $this->browse(
            function ($browser) {
                $browser->visit('/users/john.smith')
                    ->assertSee($this->user . '\'s Profile');
            }
        );
    }
}