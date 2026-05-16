<?php

namespace Tests\Feature;

use Tests\TestCase;

class HomepageTest extends TestCase
{
    public function test_homepage_loads(): void
    {
        $response = $this->get('/');

        $response
            ->assertOk()
            ->assertSee('vue-app');
    }

    public function test_vue_app_shell_loads(): void
    {
        $response = $this->get('/app');

        $response
            ->assertOk()
            ->assertSee('vue-app');
    }

    public function test_resource_pages_load_the_vue_app_shell(): void
    {
        foreach (['/resources/feedback', '/resources/pricing'] as $path) {
            $response = $this->get($path);

            $response
                ->assertOk()
                ->assertSee('vue-app');
        }
    }
}
