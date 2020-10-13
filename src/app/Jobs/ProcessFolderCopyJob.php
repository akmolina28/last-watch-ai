<?php

namespace App\Jobs;

use App\DetectionEvent;
use App\DetectionProfile;
use App\FolderCopyConfig;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessFolderCopyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected DetectionEvent $event;
    protected FolderCopyConfig $config;
    protected DetectionProfile $profile;

    /**
     * Create a new job instance.
     *
     * @param DetectionEvent $event
     * @param FolderCopyConfig $config
     * @param DetectionProfile $profile
     */
    public function __construct(DetectionEvent $event, FolderCopyConfig $config, DetectionProfile $profile)
    {
        $this->event = $event;
        $this->config = $config;
        $this->profile = $profile;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $basename = basename($this->event->image_file_name);
        $ext = pathinfo($this->event->image_file_name, PATHINFO_EXTENSION);

        if ($this->config->overwrite) {
            $basename = $this->profile->slug.'.'.$ext;
        }

        copy($this->event->image_file_name, $this->config->copy_to.$basename);
    }
}
