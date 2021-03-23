<?php

namespace App\Jobs;

use App\ImageFile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DeleteEventImageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public ImageFile $imageFile;

    /**
     * Create a new job instance.
     *
     * @param ImageFile $imageFile
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
        Log::debug('Handling DeleteEventImageJob for ImageFile #'.($this->imageFile->id ?? -1));

        // delete image and thumbnail
        $this->deleteFile($this->imageFile->getPath(false));
        $this->deleteFile($this->imageFile->getPath(true));

        // delete the model from the database
        $this->deleteRecordIfExists();
    }

    private function deleteRecordIfExists()
    {
        $id = $this->imageFile->id ?? -1;
        Log::debug('Deleting ImageFile #'.$id);

        try {
            $this->imageFile->delete();
            Log::debug('Successfully deleted ImageFile #'.$id);
        } catch (ModelNotFoundException $ex) {
            Log::debug('Unable to delete, model does not exist for ImageFile #'.$id);
        }
    }

    private function deleteFile($storagePath)
    {
        Log::debug('Deleting '.$storagePath.'...');

        $success = Storage::delete($storagePath);

        if ($success) {
            Log::debug('Successfully deleted '.$storagePath);
        } else {
            Log::debug('Unable to delete file '.$storagePath);
        }
    }
}
