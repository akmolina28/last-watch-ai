<?php

namespace App\Jobs;

use App\DetectionEvent;
use App\DetectionProfile;
use App\SmbCifsCopyConfig;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessSmbCifsCopyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $event;
    protected $config;
    protected $profile;

    /**
     * Create a new job instance.
     *
     * @param DetectionEvent $event
     * @param SmbCifsCopyConfig $config
     * @param DetectionProfile $profile
     */
    public function __construct(DetectionEvent $event, SmbCifsCopyConfig $config, DetectionProfile $profile)
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
    public function handle() //todo: resolve client from container
    {
        $localPath = $this->event->image_file_name;
        $destPath = basename($this->event->image_file_name);

        if ($this->config->overwrite) {
            $ext = pathinfo($this->event->image_file_name, PATHINFO_EXTENSION);
            $destPath = $this->profile->slug.'.'.$ext;
        }

        $cmd = 'smbclient '.$this->config->servicename.' -U '.$this->config->user.'%'.$this->config->password.' -c \'cd "'.$this->config->remote_dest.'" ; put "'.$localPath.'" "'.$destPath.'"\'';

        Log::info($cmd);

        $result = shell_exec($cmd);

        Log::info($result);
    }
}
