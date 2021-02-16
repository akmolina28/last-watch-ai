<?php

namespace App;

use Eloquent;
use Illuminate\Database\Eloquent\Model;

/**
 * AiPrediction.
 *
 * @mixin Eloquent
 */
class ImageFile extends Model
{
    protected $fillable = [
        'file_name',
        'path',
        'width',
        'height',
    ];
}
