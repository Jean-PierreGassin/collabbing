<?php

namespace App\Repositories\Users;

use App\Data\Users\ProviderConnectionData;
use App\Models\ConnectedAccount;
use App\Models\User;

class ConnectedAccountRepository
{
    public function connect(User $user, ProviderConnectionData $data): ConnectedAccount
    {
        return ConnectedAccount::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'provider' => $data->provider,
            ],
            [
                'provider_user_id' => $data->providerUserId,
                'provider_username' => $data->username,
                'token' => $data->token,
                'scopes' => $data->scopes,
                'connected_at' => now(),
            ]
        );
    }

    public function disconnect(User $user, string $provider): void
    {
        $user->connectedAccounts()
            ->where('provider', $provider)
            ->delete();
    }
}
