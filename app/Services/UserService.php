<?php

namespace App\Services;

use App\Models\ConnectedAccount;
use App\Models\User;
use App\Repositories\Users\UserRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function __construct(private UserRepository $users) {}

    public function create(array $data): User
    {
        return $this->users->create([
            'username' => $data['username'],
            'first_name' => ucwords($data['first_name']),
            'last_name' => ucwords($data['last_name']),
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }

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

            if ($key === 'password') {
                $value = Hash::make($value);
            }

            $values[$key] = $value;
        }

        return $this->users->update($user, $values);
    }

    public function connectProvider(User $user, string $provider, ?string $token, ?string $username, ?string $providerUserId = null, array $scopes = []): ConnectedAccount
    {
        return ConnectedAccount::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'provider' => $provider,
            ],
            [
                'provider_user_id' => $providerUserId,
                'provider_username' => $username,
                'token' => $token,
                'scopes' => $scopes,
                'connected_at' => now(),
            ]
        );
    }

    public function disconnectProvider(User $user, string $provider): void
    {
        $user->connectedAccounts()->where('provider', $provider)->delete();
    }
}
