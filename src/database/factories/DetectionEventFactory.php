<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\DetectionEvent;
use Faker\Generator as Faker;

$factory->define(DetectionEvent::class, function (Faker $faker) {
    return [
        'occurred_at' => $faker->dateTimeBetween('-30 days', 'now'),
        'is_processed' => true,
    ];
});
