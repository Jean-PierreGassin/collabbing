<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RouteContractTest extends TestCase
{
    public function test_implemented_resource_routes_are_registered(): void
    {
        foreach ([
            'ideas.index',
            'ideas.show',
            'ideas.create',
            'ideas.store',
            'ideas.edit',
            'ideas.update',
            'ideas.dashboard',
            'ideas.comments.create',
            'ideas.comments.store',
            'ideas.comments.edit',
            'ideas.comments.update',
            'ideas.supporters.store',
            'ideas.supporters.destroy',
            'ideas.applications.create',
            'ideas.applications.store',
            'ideas.applications.destroy',
            'ideas.applications.approve',
        ] as $routeName) {
            $this->assertTrue(Route::has($routeName), "Expected route [{$routeName}] to be registered.");
        }
    }

    public function test_unimplemented_resource_routes_are_not_registered(): void
    {
        foreach ([
            'ideas.destroy',
            'ideas.comments.index',
            'ideas.comments.show',
            'ideas.comments.destroy',
            'ideas.supporters.index',
            'ideas.supporters.show',
            'ideas.applications.index',
            'ideas.applications.show',
            'ideas.applications.edit',
            'ideas.applications.update',
        ] as $routeName) {
            $this->assertFalse(Route::has($routeName), "Unexpected route [{$routeName}] is registered.");
        }
    }

    public function test_mutating_product_routes_are_rate_limited(): void
    {
        foreach ([
            'users.update' => 'throttle:product-write',
            'ideas.store' => 'throttle:product-write',
            'ideas.update' => 'throttle:product-write',
            'ideas.comments.store' => 'throttle:product-write',
            'ideas.comments.update' => 'throttle:product-write',
            'ideas.supporters.store' => 'throttle:product-write',
            'ideas.supporters.destroy' => 'throttle:product-write',
            'ideas.applications.store' => 'throttle:product-write',
            'ideas.applications.destroy' => 'throttle:product-write',
            'ideas.applications.approve' => 'throttle:product-write',
            'ideas.repository-create' => 'throttle:integration-write',
            'ideas.repository-invite' => 'throttle:integration-write',
            'auth.github.revoke' => 'throttle:integration-write',
        ] as $routeName => $middleware) {
            $route = Route::getRoutes()->getByName($routeName);

            $this->assertNotNull($route, "Expected route [{$routeName}] to be registered.");
            $this->assertContains($middleware, $route->gatherMiddleware(), "Expected route [{$routeName}] to use [{$middleware}].");
        }
    }
}
