<?php

namespace App\Jobs;

use App\DetectionEvent;
use App\DetectionProfile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use \Spatie\WebhookClient\ProcessWebhookJob as SpatieProcessWebhookJob;

class ProcessWebhookJob extends SpatieProcessWebhookJob
{
    public function handle()
    {
        $file_name = $this->webhookCall->payload['file'];
        $path = Storage::disk('public')->path('events/'.$file_name);

        list($width, $height) = getimagesize($path);

        $event = DetectionEvent::create([ // todo: fill occurred_at
            'image_file_name' => $file_name,
            'image_dimensions' => $width.'x'.$height
        ]);

        $activeProfiles = DetectionProfile::get();
        $match = false;

        foreach($activeProfiles as $profile) {

            if ($profile->pattern_match($file_name)) {
                $match = true;

                $event->patternMatchedProfiles()->attach($profile->id);

                break;
            }
        }

        if ($match) {
            ProcessDetectionEventJob::dispatch($event);
        }
    }
}
