<?php

namespace App\Data\Users;

final readonly class UserRegistrationData
{
    public function __construct(
        public string $username,
        public string $firstName,
        public string $lastName,
        public string $email,
        public string $password
    ) {}
}
