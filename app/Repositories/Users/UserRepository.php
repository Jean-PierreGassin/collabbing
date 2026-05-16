<?php

namespace App\Repositories\Users;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class UserRepository
{
    public function all(): Collection
    {
        return User::all();
    }

    public function getByUsername(string $username): ?User
    {
        return User::whereUsername($username)->limit(1)->first();
    }

    public function update(User $user, array $data): bool
    {
        foreach ($data as $key => $value) {
            $user->{$key} = $value;
        }

        return $user->save();
    }
}
