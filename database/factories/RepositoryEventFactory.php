<?php

namespace Database\Factories;

use App\Models\CodeRepository;
use App\Models\RepositoryEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RepositoryEvent>
 */
class RepositoryEventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code_repository_id' => CodeRepository::factory(),
            'type' => 'repository_synced',
            'summary' => 'Repository sync connected.',
            'occurred_at' => now(),
            'dedupe_key' => $this->faker->unique()->sha1,
            'payload' => [],
        ];
    }
}
