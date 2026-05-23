<?php

namespace App\Http\Controllers\Auth;

use App\Data\Users\ProviderConnectionData;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UserService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\GithubProvider;
use Laravel\Socialite\Two\User as SocialiteUser;

class SocialController extends Controller
{
    public function __construct(private UserService $users)
    {
        $this->middleware('auth');
    }

    public function redirectToProvider(): RedirectResponse
    {
        return $this->githubProvider()
            ->scopes(['public_repo'])
            ->redirect();
    }

    public function handleProviderCallback(): RedirectResponse
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            return redirect()->route('login');
        }

        try {
            $providerUser = $this->githubProvider()->user();
        } catch (Exception) {
            return redirect()
                ->route('users.edit', $user->username)
                ->withErrors(['github' => 'Unable to link GitHub account']);
        }

        if (! $providerUser instanceof SocialiteUser) {
            return redirect()
                ->route('users.edit', $user->username)
                ->withErrors(['github' => 'Unable to link GitHub account']);
        }

        $providerUserId = $providerUser->getId();

        $this->users->connectProvider($user, new ProviderConnectionData(
            provider: User::PROVIDER_GITHUB,
            token: $providerUser->token,
            username: $providerUser->getNickname(),
            providerUserId: (string) $providerUserId,
            scopes: ['public_repo']
        ));

        return redirect()
            ->route('users.edit', $user->username)
            ->with('status', 'Successfully linked GitHub account');
    }

    public function revokeProvider(): RedirectResponse
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            return redirect()->route('login');
        }

        $this->users->disconnectProvider($user, User::PROVIDER_GITHUB);

        return redirect()
            ->back()
            ->with('status', 'GitHub account unlinked.');
    }

    private function githubProvider(): GithubProvider
    {
        $provider = Socialite::driver('github');

        if (! $provider instanceof GithubProvider) {
            throw new Exception('GitHub OAuth provider is not configured.');
        }

        return $provider;
    }
}
