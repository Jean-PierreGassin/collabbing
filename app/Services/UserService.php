<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

/**
 * Class UserService
 * @package App\Services
 */
class UserService
{
    /**
     * @param string $username
     * @return mixed
     */
    public function getUserByUsername(string $username)
    {
        return User::whereUsername($username)->limit(1)->first();
    }

    /**
     * @param User $user
     * @param array $data
     * @return bool
     */
    public function update(User $user, array $data): bool
    {
        // TODO: Move this to request validation
        foreach ($data as $key => $value) {
            if ($key === 'password' && $value === null) {
                unset($key);
                continue;
            }

            if ($key === 'password' && $value !== null) {
                $value = Hash::make($value);
            }

            $user->{$key} = $value;
        }

        return $user->save();
    }
}