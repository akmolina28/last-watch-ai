<?php

namespace App\Jobs;

use App\DetectionProfile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class EnableDetectionProfileJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public DetectionProfile $profile;

    /**
     * Create a new job instance.
     *
     * @param  DetectionProfile  $profile
     * @return void
     */
    public function __construct(DetectionProfile $profile)
    {
        $this->profile = $profile;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->profile->is_enabled = true;
        $this->profile->save();
    }
}
