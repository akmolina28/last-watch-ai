<?php

namespace App\Jobs;

use App\DetectionEvent;
use App\DetectionProfile;
use App\ImageFile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class ProcessEventUploadJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $path;
    public string $fileName;
    public Carbon $occurredAt;
    public array $compressionSettings;

    /**
     * Create a new job instance.
     *
     * @param $path
     * @param $fileName
     * @param Carbon $occurredAt
     * @param array $compressionSettings
     */
    public function __construct($path, $fileName, $occurredAt, array $compressionSettings = [])
    {
        $this->path = $path;
        $this->fileName = $fileName;
        $this->occurredAt = $occurredAt;
        $this->compressionSettings = $compressionSettings;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $absolutePath = Storage::path($this->path);
        [$width, $height] = getimagesize($absolutePath);

        $imageFile = ImageFile::create([
            'path' => $this->path,
            'file_name' => $this->fileName,
            'width' => $width,
            'height' => $height,
        ]);

        $event = DetectionEvent::create([
            'image_file_id' => $imageFile->id,
            'occurred_at' => $this->occurredAt,
        ]);

        $activeMatchedProfiles = $event->matchEventToProfiles(DetectionProfile::all());

        if ($activeMatchedProfiles > 0) {
            ProcessDetectionEventJob::dispatch($event, $this->compressionSettings)
                ->onQueue('medium');
        }
    }
}
