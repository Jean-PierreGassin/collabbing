<?php

namespace Database\Factories;

use App\Models\IdeaApplication;
use App\Models\IdeaApplicationMessage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<IdeaApplicationMessage> */
class IdeaApplicationMessageFactory extends Factory
{
    protected $model = IdeaApplicationMessage::class;

    public function definition(): array
    {
        return [
            'idea_application_id' => IdeaApplication::factory(),
            'user_id' => User::factory(),
            'type' => IdeaApplicationMessage::TYPE_MESSAGE,
            'body' => $this->faker->paragraph,
            'occurred_at' => now(),
        ];
    }

    public function system(string $type = IdeaApplicationMessage::TYPE_SYSTEM): static
    {
        return $this->state(fn (): array => [
            'user_id' => null,
            'type' => $type,
            'body' => null,
        ]);
    }
}
