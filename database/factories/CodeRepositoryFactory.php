<?php

namespace Database\Factories;

use App\Models\CodeRepository;
use App\Models\Idea;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CodeRepository>
 */
class CodeRepositoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $owner = $this->faker->userName;
        $name = $this->faker->slug(3);

        return [
            'idea_id' => Idea::factory(),
            'provider' => CodeRepository::PROVIDER_GITHUB,
            'status' => CodeRepository::STATUS_ACTIVE,
            'provider_repository_id' => (string) $this->faker->randomNumber(6),
            'owner' => $owner,
            'name' => $name,
            'full_name' => "{$owner}/{$name}",
            'html_url' => "https://github.com/{$owner}/{$name}",
            'default_branch' => 'main',
            'open_issues_count' => 0,
            'stargazers_count' => 0,
            'forks_count' => 0,
        ];
    }
}
