<?php

namespace App\Services\ThirdParty\GitHub;

use Github\Client;
use GrahamCampbell\GitHub\Facades\GitHub;

class GitHubService
{
    protected static array $clients = [];

    public static function createClient(string $token): Client
    {
        $cacheKey = sha1($token);

        if (! isset(self::$clients[$cacheKey])) {
            self::$clients[$cacheKey] = GitHub::getFactory()->make(
                [
                    'token' => $token,
                    'method' => 'token',
                ]
            );
        }

        return self::$clients[$cacheKey];
    }
}
