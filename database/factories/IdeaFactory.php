<?php

namespace Database\Factories;

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
}
