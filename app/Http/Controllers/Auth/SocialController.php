<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
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
        $user = User::find(Auth::user()->id);

        $gitHub = $user->createGithubClient($providerUser->token);

        $user->github_token = $providerUser->token;
        $user->github_username = $gitHub->currentUser()->show()['login'];
        $user->save();

        return redirect()
            ->route('users.edit', $user->username)
            ->with('status', 'You\'ve successfully linked your GitHub account ❤️');
    }

    /**
     * Remove the provider token for this user.
     *
     * @return RedirectResponse
     */
    public function revokeProvider(): RedirectResponse
    {
        $user = User::find(Auth::user()->id);

        $user->github_token = null;
        $user->github_username = null;
        $user->save();

        return redirect()
            ->route('users.edit', $user->username)
            ->with('status', 'You\'ve successfully un-linked your GitHub account 🤷‍♂️️');
    }
}
