<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\DetectionEvent;
use Faker\Generator as Faker;

$factory->define(DetectionEvent::class, function (Faker $faker) {
    return [
        'image_file_name' => $faker->word().'.jpg',
        'image_dimensions' => $faker->randomElement(['640x480', '1920x1080']),
        'occurred_at' => $faker->dateTimeBetween('-30 days', 'now')
    ];
});

