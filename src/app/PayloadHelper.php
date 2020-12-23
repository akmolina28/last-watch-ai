<?php


namespace App;


class PayloadHelper
{
    public static function doReplacements(
        string $payload, DetectionEvent $event, DetectionProfile $profile)
    {

        $replaced = $payload;

        $replaced = str_replace('%image_file_name%', $event->image_file_name, $replaced);

        $replaced = str_replace('%profile_name%', $profile->name, $replaced);

        $objectClasses = $profile->aiPredictions()
            ->where('detection_event_id', '=', $event->id)
            ->where('ai_prediction_detection_profile.is_masked', '=', 0)
            ->where('ai_prediction_detection_profile.is_smart_filtered', '=', 0)
            ->pluck('object_class')
            ->sort()
            ->implode(',');

        $replaced = str_replace('%object_classes%', $objectClasses, $replaced);

        $replaced = str_replace('%event_url%', url('/events/'.$event->id), $replaced);

        $replaced = str_replace('%image_url%', url('/storage/'.urlencode($event->image_file_name)), $replaced);

        return $replaced;
    }
}
