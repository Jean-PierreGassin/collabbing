<?php

use Faker\Generator as Faker;

$factory->define(
    App\Idea::class,
    function (Faker $faker) {
        return [
            'title' => $faker->title,
            'communication' => 'Slack',
            'content' => $faker->paragraph,
        ];
    }
);
