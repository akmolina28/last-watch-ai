<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\DetectionProfile;
use Faker\Generator as Faker;

$factory->define(DetectionProfile::class, function (Faker $faker) {
    return [
        'name' => $faker->unique()->words(3, true),
        'file_pattern' => $faker->word(),
        'object_classes' => $faker->randomElements(config('deepstack.object_classes'), $faker->numberBetween(1, 5)),
        'min_confidence' => $faker->numberBetween(45, 100) / 100,
        'use_regex' => $faker->boolean(),
        'use_mask' => $faker->boolean()
    ];
});
