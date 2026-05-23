<?php

namespace Database\Factories;

use App\Models\ConnectedAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<User> */
class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'username' => $this->faker->unique()->userName,
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'email' => $this->faker->unique()->safeEmail,
            'password' => '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm',
            'bio' => $this->faker->text,
            'remember_token' => Str::random(10),
        ];
    }

    public function withGithubAccount(?string $token = 'github-token', ?string $username = 'octocat'): static
    {
        return $this->afterCreating(function (User $user) use ($token, $username): void {
            ConnectedAccount::factory()->for($user)->create([
                'provider' => User::PROVIDER_GITHUB,
                'provider_username' => $username,
                'token' => $token,
            ]);
        });
    }
}
