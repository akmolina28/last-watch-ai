<?php

/** @var Factory $factory */

use App\DeepstackCall;
use App\DetectionEvent;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(DeepstackCall::class, function (Faker $faker) {
    $calledAt = $faker->dateTimeBetween('-30 days', 'now');
    $returnedAt = clone $calledAt;
    $returnedAt->add(new DateInterval('PT1S'));

    return [
        'input_file' => $faker->word().'.jpg',
        'called_at' => $calledAt,
        'returned_at' => $returnedAt,
        // @codingStandardsIgnoreLine
        'response_json' => '{"success":true,"predictions":[{"confidence":0.9995428,"label":"person","y_min":95,"x_min":295,"y_max":523,"x_max":451},{"confidence":0.9994912,"label":"person","y_min":99,"x_min":440,"y_max":531,"x_max":608},{"confidence":0.9990447,"label":"dog","y_min":358,"x_min":647,"y_max":539,"x_max":797}]}',
        'is_error' => false,
        'detection_event_id' => factory(DetectionEvent::class)->create(),
    ];
});
