<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class FormOldInputTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_old_input_is_shared_with_inertia_pages_without_sensitive_fields(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->withSession([
                '_old_input' => [
                    '_token' => 'csrf-token',
                    'password' => 'secret-password',
                    'password_confirmation' => 'secret-password',
                    'remember' => 'on',
                    'title' => 'Retained idea',
                ],
            ])
            ->get(route('ideas.create'));

        $response
            ->assertOk()
            ->assertDontSee('secret-password', false)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Ideas/Form')
                ->where('oldInput.remember', 'on')
                ->where('oldInput.title', 'Retained idea'));
    }

    public function test_validation_redirects_repopulate_idea_form_input(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from(route('ideas.create'))
            ->post(route('ideas.store'), [
                'title' => '',
                'summary' => 'A retained summary after validation fails.',
                'repository_name' => 'retained-repository',
                'communication' => 'Discord',
                'content' => 'The long-form idea pitch should still be here.',
                'status' => 'open',
            ]);

        $response
            ->assertRedirect(route('ideas.create'))
            ->assertSessionHasErrors('title')
            ->assertSessionHasInput('summary', 'A retained summary after validation fails.')
            ->assertSessionHasInput('content', 'The long-form idea pitch should still be here.');

        $this
            ->actingAs($user)
            ->get(route('ideas.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Ideas/Form')
                ->where('oldInput.summary', 'A retained summary after validation fails.')
                ->where('oldInput.repository_name', 'retained-repository')
                ->where('oldInput.content', 'The long-form idea pitch should still be here.'));
    }
}
