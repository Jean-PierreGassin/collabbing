<?php

namespace Database\Factories;

use App\Models\CodeRepository;
use App\Models\Idea;
use Illuminate\Database\Eloquent\Factories\Factory;

class IdeaFactory extends Factory
{
    protected $model = Idea::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'communication' => 'Slack',
            'content' => $this->faker->paragraph,
        ];
    }

    public function withCodeRepository(?string $name = null, array $attributes = []): static
    {
        return $this->afterCreating(function (Idea $idea) use ($name, $attributes): void {
            $idea->codeRepository()->create(array_merge([
                'provider' => CodeRepository::PROVIDER_GITHUB,
                'status' => CodeRepository::STATUS_PLANNED,
                'owner' => $idea->user?->githubUsername(),
                'name' => $name ?? $this->faker->slug(2),
            ], $attributes));
        });
    }
}
