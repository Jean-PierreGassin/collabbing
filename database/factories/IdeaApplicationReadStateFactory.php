<?php

namespace Database\Factories;

use App\Models\IdeaApplication;
use App\Models\IdeaApplicationReadState;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<IdeaApplicationReadState> */
class IdeaApplicationReadStateFactory extends Factory
{
    protected $model = IdeaApplicationReadState::class;

    public function definition(): array
    {
        return [
            'idea_application_id' => IdeaApplication::factory(),
            'user_id' => User::factory(),
            'last_read_at' => now(),
        ];
    }
}
