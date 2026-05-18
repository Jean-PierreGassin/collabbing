<?php

namespace App\Repositories\Users;

use App\Data\Users\UserProfileData;
use App\Data\Users\UserRegistrationData;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class UserRepository
{
    public function create(UserRegistrationData $data, string $passwordHash): User
    {
        return User::query()->create([
            'username' => $data->username,
            'first_name' => ucwords($data->firstName),
            'last_name' => ucwords($data->lastName),
            'email' => $data->email,
            'password' => $passwordHash,
        ]);
    }

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

    public function update(User $user, UserProfileData $data, ?string $passwordHash): bool
    {
        foreach ($data->updateAttributes($passwordHash) as $key => $value) {
            $user->{$key} = $value;
        }

        return $user->save();
    }
}
