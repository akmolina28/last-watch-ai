<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\ImageFile;

$factory->define(ImageFile::class, function () {
    return [
        'path' => 'events/'.Str::random(40).'.jpeg',
        'file_name' => 'testimage.jpg',
        'width' => 640,
        'height' => 480,
    ];
});
