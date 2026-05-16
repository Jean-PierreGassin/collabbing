<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Users\UserRepository;
use Illuminate\Support\Facades\Hash;

/**
 * Class UserService
 */
class UserService
{
    public function __construct(private UserRepository $users) {}

    public function all()
    {
        return $this->users->all();
    }

    /**
     * @return mixed
     */
    public function getUserByUsername(string $username)
    {
        return $this->users->getByUsername($username);
    }

    public function update(User $user, array $data): bool
    {
        // TODO: Move this to request validation
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
