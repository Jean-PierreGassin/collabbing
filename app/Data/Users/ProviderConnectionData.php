<?php

namespace App\Data\Users;

final readonly class ProviderConnectionData
{
    public function __construct(
        public string $provider,
        public ?string $token,
        public ?string $username,
        public ?string $providerUserId,
        public array $scopes
    ) {}
}
