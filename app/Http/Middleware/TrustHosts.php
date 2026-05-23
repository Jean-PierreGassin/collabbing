<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustHosts as Middleware;

class TrustHosts extends Middleware
{
    public function hosts(): array
    {
        $host = parse_url((string) config('app.url'), PHP_URL_HOST);

        $hosts = [];

        if (is_string($host) && $host !== '') {
            $hosts[] = '^'.preg_quote($host).'$';
        }

        if (! app()->isProduction()) {
            $hosts = array_merge($hosts, [
                '^localhost$',
                '^127\.0\.0\.1$',
                '^\[::1\]$',
            ]);
        }

        return $hosts;
    }
}
