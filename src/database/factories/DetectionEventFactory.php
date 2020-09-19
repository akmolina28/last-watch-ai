<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\DetectionEvent;
use Faker\Generator as Faker;

$factory->define(DetectionEvent::class, function (Faker $faker) {
    return [
        'image_file_name' => $faker->word().'.jpg',
        'occurred_at' => $faker->dateTimeBetween('-30 days', 'now')
    ];
});
