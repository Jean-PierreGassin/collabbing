<?php

namespace App\Services\ThirdParty\GitHub;

use Github\Client;
use GrahamCampbell\GitHub\Facades\GitHub;

class GitHubService
{
    protected static mixed $client = null;

    public static function createClient(string $token): Client
    {
        if (self::$client === null) {
            self::$client = GitHub::getFactory()->make(
                [
                    'token' => $token,
                    'method' => 'token',
                ]
            );
        }

        return self::$client;
    }
}
