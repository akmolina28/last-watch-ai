<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\TelegramConfig;
use Faker\Generator as Faker;

$factory->define(TelegramConfig::class, function (Faker $faker) {
    return [
        'name' => $faker->unique()->words(3, true),
        'token' => $faker->word(),
        'chat_id' => $faker->word()
    ];
});
