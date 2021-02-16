<?php


namespace App\Jobs;


use App\DeepstackClientInterface;
use App\DetectionEvent;
use App\DetectionProfile;
use App\ImageFile;
use Faker\Provider\Image;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
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
     * @param DeepstackClientInterface $client
     * @return void
     */
    public function handle(DeepstackClientInterface $client)
    {
        Storage::delete($this->imageFile->path);

        $this->imageFile->delete();
    }
}
