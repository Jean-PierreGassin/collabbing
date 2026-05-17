<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Inertia\Testing\AssertableInertia as Assert;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use Tests\TestCase;

class AuthSecurityTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_registration_rejects_short_passwords(): void
    {
        $response = $this->post(route('register'), $this->registrationPayload([
            'password' => 'short1',
            'password_confirmation' => 'short1',
        ]));

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_registration_rejects_commas_in_usernames(): void
    {
        $response = $this->post(route('register'), $this->registrationPayload([
            'username' => 'bad,name',
        ]));

        $response->assertSessionHasErrors('username');
        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_registration_accepts_stronger_passphrases(): void
    {
        $response = $this->post(route('register'), $this->registrationPayload());

        $response->assertRedirect('/ideas');
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'username' => 'builder_2026',
            'email' => 'builder@example.com',
        ]);
    }

    public function test_profile_update_rejects_weak_new_passwords(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from(route('users.edit', $user->username))
            ->put(route('users.update', $user->username), [
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'bio' => $user->bio,
                'password' => 'weak1',
                'password_confirmation' => 'weak1',
            ]);

        $response
            ->assertRedirect(route('users.edit', $user->username))
            ->assertSessionHasErrors('password');
    }

    public function test_missing_profile_edit_returns_not_found(): void
    {
        $user = User::factory()->create();

        $this
            ->actingAs($user)
            ->get(route('users.edit', 'missing-user'))
            ->assertNotFound();
    }

    public function test_missing_profile_update_returns_not_found(): void
    {
        $user = User::factory()->create();

        $this
            ->actingAs($user)
            ->put(route('users.update', 'missing-user'), [
                'first_name' => 'Missing',
                'last_name' => 'User',
                'email' => 'missing-user@example.com',
                'bio' => null,
            ])
            ->assertNotFound();
    }

    public function test_user_props_do_not_require_a_github_api_request_for_profile_pictures(): void
    {
        $user = User::factory()->withGithubAccount('invalid-token', 'octocat')->create();

        $response = $this->get(route('users.show', $user->username));

        $response
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Users/Show')
                ->where('user.profilePicture', 'https://github.com/octocat.png?size=200'));
    }

    public function test_login_attempts_are_throttled(): void
    {
        $user = User::factory()->create([
            'username' => 'throttlecheck',
        ]);

        for ($attempt = 0; $attempt < 6; $attempt++) {
            $response = $this->post(route('login'), [
                'username' => $user->username,
                'password' => 'not-the-password',
            ]);
        }

        $response->assertSessionHasErrors('username');
    }

    public function test_login_rejects_oversized_credentials_before_authentication(): void
    {
        $response = $this->post(route('login'), [
            'username' => str_repeat('a', 21),
            'password' => str_repeat('b', 129),
        ]);

        $response->assertSessionHasErrors(['username', 'password']);
        $this->assertGuest();
    }

    public function test_registration_attempts_are_rate_limited(): void
    {
        for ($attempt = 0; $attempt < 6; $attempt++) {
            $response = $this->post(route('register'), $this->registrationPayload([
                'username' => 'rate_limited_user',
                'email' => 'rate-limited@example.com',
                'password' => 'short1',
                'password_confirmation' => 'short1',
            ]));
        }

        $response->assertStatus(429);
    }

    public function test_password_reset_email_attempts_are_rate_limited(): void
    {
        for ($attempt = 0; $attempt < 6; $attempt++) {
            $response = $this->post(route('password.email'), [
                'email' => 'missing@example.com',
            ]);
        }

        $response->assertStatus(429);
    }

    public function test_password_reset_submission_attempts_are_rate_limited(): void
    {
        for ($attempt = 0; $attempt < 6; $attempt++) {
            $response = $this->post(route('password.update'), [
                'token' => 'invalid-token',
                'email' => 'missing@example.com',
                'password' => 'collabbing2026',
                'password_confirmation' => 'collabbing2026',
            ]);
        }

        $response->assertStatus(429);
    }

    public function test_github_revoke_requires_a_non_get_request(): void
    {
        $user = User::factory()->withGithubAccount('github-token', 'octocat')->create();

        $response = $this
            ->actingAs($user)
            ->get(route('auth.github.revoke'));

        $response->assertStatus(405);

        $user->refresh();

        $this->assertSame('github-token', $user->githubToken());
        $this->assertSame('octocat', $user->githubUsername());
    }

    public function test_github_oauth_requests_only_public_repository_scope(): void
    {
        $user = User::factory()->create();
        $provider = Mockery::mock();
        $provider->shouldReceive('scopes')
            ->once()
            ->with(['public_repo'])
            ->andReturnSelf();
        $provider->shouldReceive('redirect')
            ->once()
            ->andReturn(redirect('https://github.com/login/oauth/authorize'));

        Socialite::shouldReceive('driver')
            ->once()
            ->with('github')
            ->andReturn($provider);

        $this
            ->actingAs($user)
            ->get(route('auth.github.login'))
            ->assertRedirect('https://github.com/login/oauth/authorize');
    }

    public function test_github_tokens_are_encrypted_at_rest(): void
    {
        $user = User::factory()->withGithubAccount('github-token', 'octocat')->create();

        $storedToken = DB::table('connected_accounts')
            ->where('user_id', $user->id)
            ->where('provider', User::PROVIDER_GITHUB)
            ->value('token');

        $this->assertIsString($storedToken);
        $this->assertNotSame('github-token', $storedToken);
        $this->assertSame('github-token', $user->refresh()->githubToken());
    }

    public function test_github_callback_stores_a_provider_account(): void
    {
        $user = User::factory()->create();
        $providerUser = Mockery::mock();
        $providerUser->token = 'github-token';
        $providerUser->shouldReceive('getId')->once()->andReturn(123);
        $providerUser->shouldReceive('getNickname')->once()->andReturn('octocat');
        $provider = Mockery::mock();
        $provider->shouldReceive('user')->once()->andReturn($providerUser);

        Socialite::shouldReceive('driver')
            ->once()
            ->with('github')
            ->andReturn($provider);

        $this
            ->actingAs($user)
            ->get(route('auth.github.callback'))
            ->assertRedirect(route('users.edit', $user->username));

        $this->assertDatabaseHas('connected_accounts', [
            'user_id' => $user->id,
            'provider' => User::PROVIDER_GITHUB,
            'provider_user_id' => '123',
            'provider_username' => 'octocat',
        ]);
    }

    public function test_github_revoke_clears_the_connected_account_with_csrf_protected_delete(): void
    {
        $user = User::factory()->withGithubAccount('github-token', 'octocat')->create();

        $response = $this
            ->actingAs($user)
            ->delete(route('auth.github.revoke'));

        $response->assertRedirect();
        $this->assertDatabaseMissing('connected_accounts', [
            'user_id' => $user->id,
            'provider' => User::PROVIDER_GITHUB,
        ]);
    }

    public function test_expired_form_submissions_redirect_with_a_status_message(): void
    {
        $user = User::factory()->create();

        Route::post('/__test/expired-form', fn () => throw new TokenMismatchException);

        $response = $this
            ->actingAs($user)
            ->from(route('users.edit', $user->username))
            ->post('/__test/expired-form');

        $response
            ->assertRedirect()
            ->assertSessionHas('status', 'The page expired. Please try again.');
    }

    public function test_invalid_encrypted_payloads_clear_session_cookies_and_redirect(): void
    {
        Route::get('/__test/invalid-encrypted-payload', fn () => throw new DecryptException('The payload is invalid.'));

        $response = $this->get('/__test/invalid-encrypted-payload');

        $response
            ->assertRedirect('/__test/invalid-encrypted-payload')
            ->assertSessionHas('status', 'The session expired. Please refresh and try again.')
            ->assertCookieExpired(config('session.cookie'))
            ->assertCookieExpired('XSRF-TOKEN');
    }

    private function registrationPayload(array $overrides = []): array
    {
        return array_merge([
            'username' => 'builder_2026',
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'email' => 'builder@example.com',
            'password' => 'collabbing2026',
            'password_confirmation' => 'collabbing2026',
        ], $overrides);
    }
}
