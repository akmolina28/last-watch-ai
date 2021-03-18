<?php

namespace App\Jobs;

use App\DetectionEvent;
use App\ImageFile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class ProcessImageOptimizationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public ImageFile $imageFile;
    public bool $compressImage;
    public int $imageQuality;

    /**
     * Create a new job instance.
     *
     * @param ImageFile $imageFile
     * @param array $settings
     */
    public function __construct(ImageFile $imageFile, array $settings = [])
    {
        $this->imageFile = $imageFile;
        $this->compressImage = $settings['compress_images'] ?? true;
        $this->imageQuality = $settings['image_quality'] ?? 75;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $path = $this->imageFile->getAbsolutePath();
        $image = Image::make($path);

        // compress original
        if ($this->compressImage) {
            Log::info('compressing to '.$this->imageQuality);
            $image->interlace(true)->save($path, $this->imageQuality);
        }

        // generate thumbnail
        $thumbName = $image->filename.'-thumb.'.$image->extension;
        $thumb = $image->resize(300, 200)
            ->interlace(true)
            ->encode('jpg', $jpegQuality = $this->imageQuality);
        Storage::disk('public')->put('events/'.$thumbName, $thumb);

        // mark processing completed
        $events = DetectionEvent::where('image_file_id', $this->imageFile->id)->get();
        foreach ($events as $event) {
            $event->is_processed = true;
            $event->save();
        }
    }
}
