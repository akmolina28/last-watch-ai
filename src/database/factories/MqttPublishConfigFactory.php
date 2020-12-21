<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\MqttPublishConfig;
use Faker\Generator as Faker;

$factory->define(MqttPublishConfig::class, function (Faker $faker) {
    return [
        'name' => $faker->unique()->word(),
        'server' => $faker->ipv4,
        'port' => '1883',
        'topic' => 'mqtt/'.$faker->word(),
        'client_id' => 'lastwatch',
        'qos' => $faker->numberBetween(0, 2),
        'is_anonymous' => false,
        'username' => $faker->userName,
        'password' => $faker->password,
        'payload_json' => '{"foo":"bar"}',
    ];
});
