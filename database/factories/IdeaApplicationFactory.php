<?php

use Faker\Generator as Faker;

$factory->define(
    App\IdeaApplication::class,
    function (Faker $faker) {
        return [
            'content' => $faker->paragraph,
        ];
    }
);
