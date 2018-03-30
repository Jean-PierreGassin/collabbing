<?php

namespace Tests\Browser;

use Tests\DuskTestCase;
use Tests\Browser\Traits\SeedsDatabase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends DuskTestCase
{
    use RefreshDatabase, SeedsDatabase;

    public function testThatAUserCanSeeTheirDashboard()
    {
        $this->browse(function ($first) {
            $this->createUserWithClosedIdea();

            $first->loginAs(\App\User::find(1));
        });

        $this->browse(function (Browser $browser) {
            $browser->visit('/dashboard')
                ->assertSee('Dashboard');
        });
    }

    public function testThatAUserCanSeeTheirProfile()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/users/me')
                ->assertSee('Your Profile');
        });
    }

    public function testThatAUserCanSeeAnotherUsersProfile()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/users/john.smith')
                ->assertSee('John Smith\'s Profile');
        });
    }
}