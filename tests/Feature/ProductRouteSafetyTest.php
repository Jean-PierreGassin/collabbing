<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ProductRouteSafetyTest extends TestCase
{
    public function testUnimplementedResourceRoutesAreNotRegistered(): void
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

    public function testMutatingProductRoutesAreRateLimited(): void
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
