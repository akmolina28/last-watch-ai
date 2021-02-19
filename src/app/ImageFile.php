<?php

namespace App;

use Eloquent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

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

    public function getAbsolutePath()
    {
        return Storage::path($this->path);
    }
}
