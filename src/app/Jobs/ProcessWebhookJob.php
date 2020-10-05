<?php

namespace App\Jobs;

use App\DetectionEvent;
use App\DetectionProfile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use \Spatie\WebhookClient\ProcessWebhookJob as SpatieProcessWebhookJob;

class ProcessWebhookJob extends SpatieProcessWebhookJob
{
    public function handle(Carbon $occurred_at = null)
    {
        $base_name = $this->webhookCall->payload['file'];
        $storage_name = 'events/'.$base_name;
        $path = Storage::path($storage_name);

        list($width, $height) = getimagesize($path);

        $event = DetectionEvent::create([
            'image_file_name' => $storage_name,
            'image_dimensions' => $width.'x'.$height,
            'occurred_at' => $occurred_at ?? Date::now()
        ]);

        $activeProfiles = DetectionProfile::get();
        $match = false;

        foreach($activeProfiles as $profile) {
            $profile_active = $profile->isActive($event->occurred_at);
            $pattern_match = $profile->pattern_match($base_name);

            if ($pattern_match) {

                if ($profile_active) {
                    $match = true;
                }

                $event->patternMatchedProfiles()->attach($profile->id, ['is_profile_active' => $profile_active]);
            }
        }

        if ($match) {
            ProcessDetectionEventJob::dispatch($event);
        }
    }
}
