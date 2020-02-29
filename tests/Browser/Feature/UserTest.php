<?php

namespace Tests\Browser\Feature;

use App\User;
use Tests\DuskTestCase;

class UserTest extends DuskTestCase
{
    /**
     * @test
     */
    public function guests_can_register(): void
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

    /**
     * @test
     */
    public function guests_can_login(): void
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

    /**
     * @test
     */
    public function user_can_update_their_profile(): void
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

    /**
     * @test
     */
    public function user_can_logout(): void
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