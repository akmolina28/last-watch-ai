<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\AiPrediction;
use Faker\Generator as Faker;

$factory->define(AiPrediction::class, function (Faker $faker) {
    $x_min = $faker->numberBetween(0, 520);
    $x_max = $x_min + $faker->numberBetween(60, 120);
    $y_min = $faker->numberBetween(0, 360);
    $y_max = $y_min + $faker->numberBetween(60, 120);

    return [
        'object_class' => $faker->randomElement(config('deepstack.object_classes')),
        'confidence' => $faker->numberBetween(10, 100) / 100,
        'x_min' => $x_min,
        'x_max' => $x_max,
        'y_min' => $y_min,
        'y_max' => $y_max
    ];
});
