<?php

namespace Database\Factories;

use App\Models\IdeaApplication;
use Illuminate\Database\Eloquent\Factories\Factory;

class IdeaApplicationFactory extends Factory
{
    protected $model = IdeaApplication::class;

    public function definition(): array
    {
        return [
            'content' => $this->faker->paragraph,
        ];
    }
}
