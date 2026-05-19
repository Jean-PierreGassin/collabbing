<?php

namespace Tests\Feature\Runtime;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    public function testSessionCookieConfigurationHasSecurityDefaults(): void
    {
        $this->assertTrue(config('session.http_only'));
        $this->assertSame('lax', config('session.same_site'));
    }

    #[DataProvider('webResponseSecurityHeaders')]
    public function testWebResponsesIncludeSecurityHeaders(string $path, int $status, array $expectedHeaders, array $missingHeaders): void
    {
        $response = $this->get($path);

        $response->assertStatus($status);

        foreach ($expectedHeaders as $header => $value) {
            $response->assertHeader($header, $value);
        }

        foreach ($missingHeaders as $header) {
            $response->assertHeaderMissing($header);
        }
    }

    public function testSecureWebResponsesIncludeStrictTransportSecurity(): void
    {
        $this->get('https://localhost/')
            ->assertOk()
            ->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
    }

    public static function webResponseSecurityHeaders(): array
    {
        return [
            'home page' => [
                '/',
                200,
                [
                    'Content-Security-Policy' => "base-uri 'self'; frame-ancestors 'none'; object-src 'none'",
                    'Cross-Origin-Opener-Policy' => 'same-origin',
                    'Permissions-Policy' => 'camera=(), microphone=(), geolocation=()',
                    'Referrer-Policy' => 'strict-origin-when-cross-origin',
                    'X-Content-Type-Options' => 'nosniff',
                    'X-Frame-Options' => 'DENY',
                ],
                [
                    'Strict-Transport-Security',
                    'X-Powered-By',
                ],
            ],
            'not found page' => [
                '/missing-page',
                404,
                [
                    'Content-Security-Policy' => "base-uri 'self'; frame-ancestors 'none'; object-src 'none'",
                    'X-Content-Type-Options' => 'nosniff',
                    'X-Frame-Options' => 'DENY',
                ],
                [],
            ],
        ];
    }
}
