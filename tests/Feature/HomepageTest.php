<?php

namespace Tests\Feature;

use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class HomepageTest extends TestCase
{
    public function test_homepage_loads(): void
    {
        $response = $this->get('/');

        $response
            ->assertOk()
            ->assertDontSee('@inertia', false)
            ->assertInertia(fn (Assert $page) => $page->component('Home'));
    }

    public function test_app_namespace_redirects_home(): void
    {
        $response = $this->get('/app');

        $response->assertRedirect(route('home'));
    }

    public function test_error_pages_include_shared_shell_props(): void
    {
        $response = $this->get('/missing-page');

        $response
            ->assertNotFound()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Error')
                ->where('status', 404)
                ->has('auth.user')
                ->has('routes.ideas')
                ->has('flash.status'));
    }

    public function test_resource_pages_load_the_inertia_app_shell(): void
    {
        foreach ([
            '/resources/feedback' => 'Feedback',
            '/resources/pricing' => 'Pricing',
        ] as $path => $component) {
            $response = $this->get($path);

            $response
                ->assertOk()
                ->assertDontSee('@inertia', false)
                ->assertInertia(fn (Assert $page) => $page->component($component));
        }
    }
}
