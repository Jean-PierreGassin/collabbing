<?php

namespace Tests\Feature\Pages;

use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\DataProvider;
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

    #[DataProvider('resourcePages')]
    public function testResourcePagesLoadTheInertiaAppShell(string $path, string $component): void
    {
        $response = $this->get($path);

        $response
            ->assertOk()
            ->assertDontSee('@inertia', false)
            ->assertInertia(fn (Assert $page) => $page->component($component));
    }

    public static function resourcePages(): array
    {
        return [
            'feedback' => ['/resources/feedback', 'Feedback'],
            'contact' => ['/resources/contact', 'Contact'],
            'pricing' => ['/resources/pricing', 'Pricing'],
        ];
    }
}
