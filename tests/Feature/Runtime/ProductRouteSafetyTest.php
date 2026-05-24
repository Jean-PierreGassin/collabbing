<?php

namespace Tests\Feature\Runtime;

use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ProductRouteSafetyTest extends TestCase
{
    #[DataProvider('unimplementedResourceRoutes')]
    public function testUnimplementedResourceRoutesAreNotRegistered(string $routeName): void
    {
        $this->assertFalse(Route::has($routeName), "Unexpected route [{$routeName}] is registered.");
    }

    #[DataProvider('mutatingProductRoutes')]
    public function testMutatingProductRoutesAreRateLimited(string $routeName, string $middleware): void
    {
        $route = Route::getRoutes()->getByName($routeName);

        $this->assertNotNull($route, "Expected route [{$routeName}] to be registered.");
        $this->assertContains($middleware, $route->gatherMiddleware(), "Expected route [{$routeName}] to use [{$middleware}].");
    }

    public static function unimplementedResourceRoutes(): array
    {
        return [
            'idea deletion' => ['ideas.destroy'],
            'comment index' => ['ideas.comments.index'],
            'comment show' => ['ideas.comments.show'],
            'comment deletion' => ['ideas.comments.destroy'],
            'supporter index' => ['ideas.supporters.index'],
            'supporter show' => ['ideas.supporters.show'],
            'application index' => ['ideas.applications.index'],
            'application show' => ['ideas.applications.show'],
        ];
    }

    public static function mutatingProductRoutes(): array
    {
        return [
            'profile update' => ['users.update', 'throttle:product-write'],
            'idea create' => ['ideas.store', 'throttle:product-write'],
            'idea update' => ['ideas.update', 'throttle:product-write'],
            'comment create' => ['ideas.comments.store', 'throttle:product-write'],
            'comment update' => ['ideas.comments.update', 'throttle:product-write'],
            'supporter create' => ['ideas.supporters.store', 'throttle:product-write'],
            'supporter delete' => ['ideas.supporters.destroy', 'throttle:product-write'],
            'application create' => ['ideas.applications.store', 'throttle:product-write'],
            'application update' => ['ideas.applications.update', 'throttle:product-write'],
            'application delete' => ['ideas.applications.destroy', 'throttle:product-write'],
            'application approval' => ['ideas.applications.approve', 'throttle:product-write'],
            'repository create' => ['ideas.repository-create', 'throttle:integration-write'],
            'repository invite' => ['ideas.repository-invite', 'throttle:integration-write'],
            'github revoke' => ['auth.github.revoke', 'throttle:integration-write'],
        ];
    }
}
