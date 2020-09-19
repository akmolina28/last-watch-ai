<?php

namespace App\Jobs;

use App\DetectionEvent;
use App\DetectionProfile;
use Illuminate\Support\Facades\Log;
use \Spatie\WebhookClient\ProcessWebhookJob as SpatieProcessWebhookJob;

class ProcessWebhookJob extends SpatieProcessWebhookJob
{
    public function handle()
    {
        $activeProfiles = DetectionProfile::get();
        $file_name = $this->webhookCall->payload['file'];

        foreach($activeProfiles as $profile) {

            if ($profile->pattern_match($file_name)) {

                $event = DetectionEvent::create([ // todo: fill occurred_at
                    'image_file_name' => $file_name
                ]);

                ProcessDetectionEventJob::dispatch($event);

                break;
            }
        }
    }
}
