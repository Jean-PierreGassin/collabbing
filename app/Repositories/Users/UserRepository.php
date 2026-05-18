<?php

namespace App\Repositories\Users;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class UserRepository
{
    public function all(): LengthAwarePaginator
    {
        return User::query()
            ->orderBy('username')
            ->paginate(24);
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
