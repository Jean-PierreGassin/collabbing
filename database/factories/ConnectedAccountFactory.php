<?php

namespace Database\Factories;

use App\Models\ConnectedAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ConnectedAccount>
 */
class ConnectedAccountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'provider' => User::PROVIDER_GITHUB,
            'provider_user_id' => (string) $this->faker->randomNumber(6),
            'provider_username' => $this->faker->unique()->userName,
            'token' => $this->faker->sha1,
            'scopes' => ['public_repo'],
            'connected_at' => now(),
        ];
    }
}
