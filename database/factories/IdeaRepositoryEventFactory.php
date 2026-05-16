<?php

namespace Database\Factories;

use App\Models\Idea;
use App\Models\IdeaRepositoryEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<IdeaRepositoryEvent>
 */
class IdeaRepositoryEventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'idea_id' => Idea::factory(),
            'type' => 'repository_synced',
            'summary' => 'Repository sync connected to GitHub.',
            'occurred_at' => now(),
            'dedupe_key' => $this->faker->unique()->sha1,
            'payload' => [],
        ];
    }
}
