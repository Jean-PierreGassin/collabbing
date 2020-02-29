<?php

namespace Tests\Browser\Feature;

use App\User;
use Tests\DuskTestCase;

class UserTest extends DuskTestCase
{
    public function testThatAGuestCanRegister()
    {
        $this->browse(
            function ($browser) {
                $browser->visit('/users/register')
                    ->type('first_name', 'John')
                    ->type('last_name', 'Smith')
                    ->type('email', 'john.smith@apples.com')
                    ->type('password', 'unbreakable')
                    ->press('Register')
                    ->assertPathIs('/dashboard');
            }
        );
    }

    public function testThatAGuestCanLogin()
    {
        $this->browse(
            function ($browser) {
                $browser->visit('/users/login')
                    ->type('email', 'john.smith@apples.com')
                    ->type('password', 'unbreakable')
                    ->press('Login')
                    ->assertPathIs('/dashboard');
            }
        );
    }

    public function testThatAUserCanUpdateTheirProfile()
    {
        $this->browse(
            function ($first) {
                $first->loginAs(User::inRandomOrder()->first());
            }
        );

        $this->browse(
            function ($browser) {
                $browser->visit('/users/me/edit')
                    ->type('first_name', 'James')
                    ->type('last_name', 'Jones')
                    ->press('Update')
                    ->assertPathIs('/users/me');
            }
        );
    }

    public function testThatAUserCanLogout()
    {
        $this->browse(
            function ($browser) {
                $browser->visit('/')
                    ->press('Logout')
                    ->assertPathIs('/');
            }
        );
    }
}