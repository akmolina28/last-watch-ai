<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\ProfileGroup;
use Faker\Generator as Faker;

$factory->define(ProfileGroup::class, function (Faker $faker) {
    return [
        'name' => $faker->unique()->words(3, true),
    ];
});
