<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\SmbCifsCopyConfig;
use Faker\Generator as Faker;

$factory->define(SmbCifsCopyConfig::class, function (Faker $faker) {
    return [
        'name' => $faker->word(),
        'servicename' => '//'.$faker->ipv4.'/'.$faker->word,
        'user' => $faker->word(),
        'password' => $faker->word(),
        'remote_dest' => '/'.$faker->word.'/'.$faker->word(),
        'overwrite' => $faker->boolean()
    ];
});
