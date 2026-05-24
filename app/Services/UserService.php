<?php

namespace App\Services;

use App\Data\Users\ProviderConnectionData;
use App\Data\Users\UserProfileData;
use App\Data\Users\UserRegistrationData;
use App\Models\ConnectedAccount;
use App\Models\User;
use App\Repositories\Users\ConnectedAccountRepository;
use App\Repositories\Users\UserRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function __construct(
        private UserRepository $users,
        private ConnectedAccountRepository $connectedAccounts
    ) {}

    public function create(UserRegistrationData $data): User
    {
        return $this->users->create($data, Hash::make($data->password));
    }

    public function all(): LengthAwarePaginator
    {
        return $this->users->all();
    }

    public function getUserByUsername(string $username): ?User
    {
        return $this->users->getByUsername($username);
    }

    public function update(User $user, UserProfileData $data): bool
    {
        $passwordHash = null;

        if ($data->password) {
            $passwordHash = Hash::make($data->password);
        }

        return $this->users->update($user, $data, $passwordHash);
    }

    public function connectProvider(User $user, ProviderConnectionData $data): ConnectedAccount
    {
        return $this->connectedAccounts->connect($user, $data);
    }

    public function disconnectProvider(User $user, string $provider): void
    {
        $this->connectedAccounts->disconnect($user, $provider);
    }
}
