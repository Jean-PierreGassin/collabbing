<?php

namespace Database\Factories;

use App\Models\CodeRepository;
use App\Models\Idea;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Idea> */
class IdeaFactory extends Factory
{
    protected $model = Idea::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'summary' => $this->faker->sentence(14),
            'communication' => 'Slack',
            'content' => $this->faker->paragraph,
        ];
    }

    public function withCodeRepository(?string $name = null, array $attributes = []): static
    {
        return $this->afterCreating(function (Idea $idea) use ($name, $attributes): void {
            $owner = $idea->user;

            $idea->codeRepository()->create(array_merge([
                'provider' => CodeRepository::PROVIDER_GITHUB,
                'status' => CodeRepository::STATUS_PLANNED,
                'owner' => $owner instanceof User ? $owner->githubUsername() : null,
                'name' => $name ?? $this->faker->slug(2),
            ], $attributes));
        });
    }
}
