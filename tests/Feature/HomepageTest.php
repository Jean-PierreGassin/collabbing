<?php

namespace Tests\Feature;

use Tests\TestCase;

class HomepageTest extends TestCase
{
    public function test_homepage_loads(): void
    {
        $response = $this->get('/');

        $response->assertOk();
    }
}
