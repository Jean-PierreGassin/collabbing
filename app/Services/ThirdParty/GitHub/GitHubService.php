<?php

namespace App\Services\ThirdParty\GitHub;

use Github\Client;
use GrahamCampbell\GitHub\Facades\GitHub;

class GitHubService
{
    protected static mixed $client = null;

    /**
     * @param string $token
     * @return Client
     */
    public static function createClient(string $token): Client
    {
        if (null === self::$client) {
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