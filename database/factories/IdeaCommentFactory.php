<?php

use Faker\Generator as Faker;

$factory->define(App\IdeaComment::class, function (Faker $faker) {
    return [
        'content' => $faker->paragraph,
    ];
});
