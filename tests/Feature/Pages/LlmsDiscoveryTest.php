<?php

namespace Tests\Feature\Pages;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class LlmsDiscoveryTest extends TestCase
{
    public function testLlmsFileIsStructuredForAgents(): void
    {
        $content = $this->llmsContent();

        $this->assertStringStartsWith("# Collabbing\n\n> ", $content);
        $this->assertStringContainsString('## Product Pages', $content);
        $this->assertStringContainsString('## Support Pages', $content);
        $this->assertStringContainsString('## Machine-Readable Files', $content);
        $this->assertMatchesRegularExpression('/^- \[[^\]]+\]\([^)]+\): .+$/m', $content);
    }

    #[DataProvider('publicLinks')]
    public function testPublicLinksResolve(string $path): void
    {
        $this->assertStringContainsString("]({$path})", $this->llmsContent());

        if (Str::startsWith($path, '/resources/')) {
            $this->get($path)->assertOk();

            return;
        }

        if ($path === '/' || $path === '/ideas' || $path === '/users') {
            $route = app('router')->getRoutes()->match(Request::create($path, 'GET'));

            $this->assertContains('web', $route->gatherMiddleware());

            return;
        }

        $this->assertFileExists(public_path(ltrim($path, '/')));
    }

    public static function publicLinks(): array
    {
        return [
            'home' => ['/'],
            'ideas' => ['/ideas'],
            'members' => ['/users'],
            'pricing' => ['/resources/pricing'],
            'feedback' => ['/resources/feedback'],
            'contact' => ['/resources/contact'],
            'robots' => ['/robots.txt'],
            'manifest' => ['/site.webmanifest'],
        ];
    }

    private function llmsContent(): string
    {
        $content = file_get_contents(public_path('llms.txt'));

        $this->assertIsString($content);

        return $content;
    }
}
