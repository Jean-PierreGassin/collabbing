<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Inertia\Testing\AssertableInertia as Assert;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\GithubProvider;
use Laravel\Socialite\Two\User as SocialiteUser;
use Tests\TestCase;

class AuthSecurityTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function testRegistrationRejectsShortPasswords(): void
    {
        $response = $this->post(route('register'), $this->registrationPayload([
            'password' => 'short1',
            'password_confirmation' => 'short1',
        ]));

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    public function testRegistrationRejectsCommasInUsernames(): void
    {
        $response = $this->post(route('register'), $this->registrationPayload([
            'username' => 'bad,name',
        ]));

        $response->assertSessionHasErrors('username');
        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    public function testRegistrationAcceptsStrongerPassphrases(): void
    {
        $response = $this->post(route('register'), $this->registrationPayload());

        $response->assertRedirect('/ideas');
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'username' => 'builder_2026',
            'email' => 'builder@example.com',
        ]);
    }

    public function testProfileUpdateRejectsWeakNewPasswords(): void
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

    public function testMissingProfileEditReturnsNotFound(): void
    {
        $user = User::factory()->create();

        $this
            ->actingAs($user)
            ->get(route('users.edit', 'missing-user'))
            ->assertNotFound();
    }

    public function testMissingProfileUpdateReturnsNotFound(): void
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

    public function testUserPropsDoNotRequireAGithubApiRequestForProfilePictures(): void
    {
        $user = User::factory()->withGithubAccount('invalid-token', 'octocat')->create();

        $response = $this->get(route('users.show', $user->username));

        $response
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Users/Show')
                ->where('user.profilePicture', 'https://github.com/octocat.png?size=200'));
    }

    public function testLoginAttemptsAreThrottled(): void
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

    public function testLoginRejectsOversizedCredentialsBeforeAuthentication(): void
    {
        $response = $this->post(route('login'), [
            'username' => str_repeat('a', 21),
            'password' => str_repeat('b', 129),
        ]);

        $response->assertSessionHasErrors(['username', 'password']);
        $this->assertGuest();
    }

    public function testRegistrationAttemptsAreRateLimited(): void
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

    public function testPasswordResetEmailAttemptsAreRateLimited(): void
    {
        for ($attempt = 0; $attempt < 6; $attempt++) {
            $response = $this->post(route('password.email'), [
                'email' => 'missing@example.com',
            ]);
        }

        $response->assertStatus(429);
    }

    public function testPasswordResetSubmissionAttemptsAreRateLimited(): void
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

    public function testGithubRevokeRequiresANonGetRequest(): void
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

    public function testGithubOauthRequestsOnlyPublicRepositoryScope(): void
    {
        $user = User::factory()->create();
        $provider = $this->createMock(GithubProvider::class);
        $provider->expects($this->once())
            ->method('scopes')
            ->with(['public_repo'])
            ->willReturnSelf();
        $provider->expects($this->once())
            ->method('redirect')
            ->willReturn(redirect('https://github.com/login/oauth/authorize'));

        Socialite::shouldReceive('driver')
            ->with('github')
            ->andReturn($provider);

        $this
            ->actingAs($user)
            ->get(route('auth.github.login'))
            ->assertRedirect('https://github.com/login/oauth/authorize');
    }

    public function testGithubTokensAreEncryptedAtRest(): void
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

    public function testGithubCallbackStoresAProviderAccount(): void
    {
        $user = User::factory()->create();
        $providerUser = (new SocialiteUser)
            ->setToken('github-token')
            ->map([
                'id' => 123,
                'nickname' => 'octocat',
            ]);
        $provider = $this->createMock(GithubProvider::class);
        $provider->expects($this->once())
            ->method('user')
            ->willReturn($providerUser);

        Socialite::shouldReceive('driver')
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

    public function testGithubRevokeClearsTheConnectedAccountWithCsrfProtectedDelete(): void
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

    public function testExpiredFormSubmissionsRedirectWithAStatusMessage(): void
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

    public function testInvalidEncryptedPayloadsClearSessionCookiesAndRedirect(): void
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
