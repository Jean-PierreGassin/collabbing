<?php

namespace App\Services\ThirdParty\GitHub;

use Github\Client;
use GrahamCampbell\GitHub\Facades\GitHub;

class GitHubClientFactory
{
    private array $clients = [];

    public function create(string $token): Client
    {
        $cacheKey = sha1($token);

        if (! isset($this->clients[$cacheKey])) {
            $this->clients[$cacheKey] = GitHub::getFactory()->make(
                [
                    'token' => $token,
                    'method' => 'token',
                ]
            );
        }

        return $this->clients[$cacheKey];
    }
}
