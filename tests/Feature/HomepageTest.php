<?php

namespace Tests\Feature;

use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class HomepageTest extends TestCase
{
    public function testHomepageLoads(): void
    {
        $response = $this->get('/');

        $response
            ->assertOk()
            ->assertDontSee('@inertia', false)
            ->assertInertia(fn (Assert $page) => $page->component('Home'));
    }

    public function testAppNamespaceRedirectsHome(): void
    {
        $response = $this->get('/app');

        $response->assertRedirect(route('home'));
    }

    public function testErrorPagesIncludeSharedShellProps(): void
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

    public function testResourcePagesLoadTheInertiaAppShell(): void
    {
        foreach ([
            '/resources/feedback' => 'Feedback',
            '/resources/contact' => 'Contact',
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
