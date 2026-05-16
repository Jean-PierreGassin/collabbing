<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Users\UserRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

/**
 * Class UserService
 */
class UserService
{
    public function __construct(private UserRepository $users) {}

    public function all(): LengthAwarePaginator
    {
        return $this->users->all();
    }

    public function getUserByUsername(string $username): ?User
    {
        return $this->users->getByUsername($username);
    }

    public function update(User $user, array $data): bool
    {
        $values = [];

        foreach ($data as $key => $value) {
            if ($key === 'password' && $value === null) {
                continue;
            }

            if ($key === 'password' && $value !== null) {
                $value = Hash::make($value);
            }

            $values[$key] = $value;
        }

        return $this->users->update($user, $values);
    }
}
