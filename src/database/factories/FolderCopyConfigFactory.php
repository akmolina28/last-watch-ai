<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\FolderCopyConfig;
use Faker\Generator as Faker;

$factory->define(FolderCopyConfig::class, function (Faker $faker) {
    return [
        'name' => $faker->unique()->words(2, true),
        'copy_to' => '/'.$faker->word(),
        'overwrite' => $faker->boolean(),
    ];
});
