<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UserService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

/**
 * Class SocialController
 */
class SocialController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(private UserService $users)
    {
        $this->middleware('auth');
    }

    /**
     * Redirect the user to the GitHub authentication page.
     */
    public function redirectToProvider(): RedirectResponse
    {
        return Socialite::driver('github')
            ->scopes(['repo'])
            ->redirect();
    }

    /**
     * Obtain the user information from GitHub.
     */
    public function handleProviderCallback(): RedirectResponse
    {
        /* @var $user User */
        $user = Auth::user();

        try {
            $providerUser = Socialite::driver('github')->user();
        } catch (Exception) {
            return redirect()
                ->route('users.edit', $user->username)
                ->with('errors', collect('Unable to link GitHub account'));
        }

        $this->users->update($user, [
            'github_token' => $providerUser->token,
            'github_username' => $providerUser->getNickname(),
        ]);

        return redirect()
            ->route('users.edit', $user->username)
            ->with('status', 'Successfully linked GitHub account');
    }

    /**
     * Remove the provider token for this user.
     */
    public function revokeProvider(): RedirectResponse
    {
        /* @var $user User */
        $user = Auth::user();
        $this->users->update($user, [
            'github_token' => null,
            'github_username' => null,
        ]);

        return redirect()
            ->back()
            ->with('status', 'Successfully un-linked GitHub account');
    }
}
