<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

/**
 * Class SocialController
 * @package App\Http\Controllers\Auth
 */
class SocialController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Redirect the user to the GitHub authentication page.
     *
     * @return RedirectResponse
     */
    public function redirectToProvider(): RedirectResponse
    {
        return Socialite::driver('github')
            ->scopes(['repo'])
            ->redirect();
    }

    /**
     * Obtain the user information from GitHub.
     *
     * @return RedirectResponse
     */
    public function handleProviderCallback(): RedirectResponse
    {
        $providerUser = Socialite::driver('github')->user();

        /* @var $user User */
        $user = Auth::user();
        $user->update(
            [
                'github_token' => $providerUser->token,
                'github_username' => $providerUser->getNickname(),
            ]
        );

        return redirect()
            ->route('users.edit', $user->username)
            ->with('status', 'Successfully linked GitHub account');
    }

    /**
     * Remove the provider token for this user.
     *
     * @return RedirectResponse
     */
    public function revokeProvider(): RedirectResponse
    {
        /* @var $user User */
        $user = Auth::user();
        $user->update(
            [
                'github_token' => null,
                'github_username' => null,
            ]
        );

        return redirect()
            ->back()
            ->with('status', 'Successfully un-linked GitHub account');
    }
}
