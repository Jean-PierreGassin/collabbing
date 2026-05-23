<?php

namespace App\Data\Users;

final readonly class UserProfileData
{
    public function __construct(
        public string $firstName,
        public string $lastName,
        public string $email,
        public ?string $bio,
        public ?string $password
    ) {}

    public function updateAttributes(?string $passwordHash): array
    {
        $attributes = [
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'email' => $this->email,
            'bio' => $this->bio,
        ];

        if ($passwordHash) {
            $attributes['password'] = $passwordHash;
        }

        return $attributes;
    }
}
