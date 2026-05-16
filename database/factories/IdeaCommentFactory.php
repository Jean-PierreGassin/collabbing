<?php

namespace Database\Factories;

use App\Models\IdeaComment;
use Illuminate\Database\Eloquent\Factories\Factory;

class IdeaCommentFactory extends Factory
{
    protected $model = IdeaComment::class;

    public function definition(): array
    {
        return [
            'content' => $this->faker->paragraph,
        ];
    }
}
