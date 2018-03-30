<?php

namespace Tests\Browser\Feature;

use Tests\DuskTestCase;
use Tests\Browser\Traits\SeedsDatabase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserActionTest extends DuskTestCase
{
    use RefreshDatabase, SeedsDatabase;

    public function testThatAGuestCanRegister()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/users/register')
                ->type('first_name', 'John')
                ->type('last_name', 'Smith')
                ->type('email', 'john.smith@apples.com')
                ->type('password', 'unbreakable')
                ->press('Register')
                ->assertPathIs('/dashboard');
        });
    }

    public function testThatAGuestCanLogin()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/users/login')
                ->type('email', 'john.smith@apples.com')
                ->type('password', 'unbreakable')
                ->press('Login')
                ->assertPathIs('/dashboard');
        });
    }

    public function testThatAUserCanUpdateTheirProfile()
    {
        $this->browse(function ($first) {
            $this->createUserWithClosedIdea();

            $first->loginAs(\App\User::find(1));
        });

        $this->browse(function (Browser $browser) {
            $browser->visit('/users/me/edit')
                ->type('first_name', 'James')
                ->type('last_name', 'Jones')
                ->press('Update')
                ->assertPathIs('/users/me');
        });
    }

    public function testThatAUserCanLogout()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->press('Logout')
                ->assertPathIs('/');
        });
    }
}