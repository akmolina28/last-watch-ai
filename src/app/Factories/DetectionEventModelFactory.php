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
     * @param String $imageFileName
     * @param Carbon|null $occurred_at
     * @return DetectionEvent|Model
     */
    public static function createFromImageFile(String $imageFileName, Carbon $occurred_at = null)
    {
        $storage_name = 'events/'.$imageFileName;
        $path = Storage::path($storage_name);

        list($width, $height) = getimagesize($path);

        return DetectionEvent::create([
            'image_file_name' => $storage_name,
            'image_dimensions' => $width.'x'.$height,
            'occurred_at' => $occurred_at ?? Date::now()
        ]);
    }
}
