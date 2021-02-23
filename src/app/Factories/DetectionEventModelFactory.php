<?php

namespace App\Factories;

use App\DetectionEvent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Storage;

class DetectionEventModelFactory
{
    /**
     * @param string $imageFileName
     * @param Carbon|null $occurred_at
     * @return DetectionEvent|Model
     */
    public static function createFromImageFile(string $imageFileName, Carbon $occurred_at = null)
    {
        $storage_name = $imageFileName;
        $path = Storage::path($storage_name);

        [$width, $height] = getimagesize($path);

        return DetectionEvent::create([
            'image_file_name' => $storage_name,
            'image_dimensions' => $width.'x'.$height,
            'occurred_at' => $occurred_at ?? Date::now(),
        ]);
    }
}
