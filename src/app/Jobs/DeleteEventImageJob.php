<?php

namespace App\Jobs;

use App\ImageFile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
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
        File::delete($this->imageFile->getAbsolutePath(false));
        File::delete($this->imageFile->getAbsolutePath(true));

        $this->imageFile->delete();
    }
}
