<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class UserDirectoryTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_user_directory_is_paginated_and_does_not_expose_email_addresses(): void
    {
        foreach (range(1, 25) as $index) {
            User::factory()->create([
                'username' => sprintf('member-%02d', $index),
                'email' => sprintf('member-%02d@example.com', $index),
            ]);
        }

        $this->get(route('users.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Users/Index')
                ->where('users.currentPage', 1)
                ->where('users.lastPage', 2)
                ->has('users.items', 24)
                ->where('users.items.0.username', 'member-01')
                ->where('users.items.0.email', null)
                ->where('routes.users', route('users.index'))
            );
    }

    public function test_user_directory_second_page_uses_remaining_members(): void
    {
        foreach (range(1, 25) as $index) {
            User::factory()->create([
                'username' => sprintf('member-%02d', $index),
                'email' => sprintf('member-%02d@example.com', $index),
            ]);
        }

        $this->get(route('users.index', ['page' => 2]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Users/Index')
                ->where('users.currentPage', 2)
                ->where('users.lastPage', 2)
                ->has('users.items', 1)
                ->where('users.items.0.username', 'member-25')
            );
    }
}
