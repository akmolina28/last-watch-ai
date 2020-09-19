<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\DetectionProfile;
use Faker\Generator as Faker;

$factory->define(DetectionProfile::class, function (Faker $faker) {
    return [
        'name' => $faker->words(3, true),
        'file_pattern' => $faker->word(),
        'object_classes' => $faker->randomElements(config('app.deepstack_object_classes')),
        'min_confidence' => $faker->numberBetween(45, 100) / 100,
        'use_regex' => false,
        'use_mask' => $faker->boolean()
    ];
});
