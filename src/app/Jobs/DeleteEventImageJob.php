<?php

namespace App\Jobs;

use App\ImageFile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class DeleteEventImageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Delete the job if its models no longer exist.
     *
     * @var bool
     */
    public $deleteWhenMissingModels = true;

    public ImageFile $imageFile;

    /**
     * Create a new job instance.
     *
     * @param  ImageFile  $imageFile
     */
    public function __construct(ImageFile $imageFile)
    {
        $this->imageFile = $imageFile;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // delete image and thumbnail
        $this->deleteFile($this->imageFile->getPath(false));
        $this->deleteFile($this->imageFile->getPath(true));

        // delete the model from the database
        $this->deleteRecordIfExists();
    }

    private function deleteRecordIfExists()
    {
        return $this->imageFile->delete();
    }

    private function deleteFile($storagePath)
    {
        return Storage::delete($storagePath);
    }
}
