<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\WebRequestConfig;
use Faker\Generator as Faker;

$factory->define(WebRequestConfig::class, function (Faker $faker) {
    return [
        'name' => $faker->word(),
        'url' => $faker->url
    ];
});
