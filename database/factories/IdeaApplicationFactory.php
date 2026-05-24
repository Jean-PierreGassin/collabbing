<?php

namespace Database\Factories;

use App\Models\Idea;
use App\Models\IdeaApplication;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<IdeaApplication> */
class IdeaApplicationFactory extends Factory
{
    protected $model = IdeaApplication::class;

    public function definition(): array
    {
        return [
            'idea_id' => Idea::factory(),
            'user_id' => User::factory(),
            'content' => $this->faker->paragraph,
            'contribution_type' => Idea::HELP_BACKEND,
            'first_action' => 'I can review the API shape and suggest a first endpoint.',
            'status' => IdeaApplication::STATUS_PENDING,
        ];
    }
}
