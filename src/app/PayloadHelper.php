<?php

namespace App;

use Exception;

class PayloadHelper
{
    public static $availableReplacements = [
        '%image_file_name%' => 'The original jpeg file name, e.g. mycamera_2021010203154512.jpg',
        '%profile_name%' => 'The name of the profile which triggered this automation',
        '%object_classes%' => 'The objects which triggered this automation, e.g. car,person,dog',
        '%event_url%' => 'Link to the event details page',
        '%image_url%' => 'Direct link to the event image on the web server',
        '%thumb_url%' => 'Direct link to the event image thumbnail on the web server',
        '%image_download_link%' => 'Resource link for downloading the image file',
    ];

    public static function doReplacement(
        string $payload,
        DetectionEvent $event,
        DetectionProfile $profile,
        string $replacementString
    ) {
        if ($replacementString == '%image_file_name%') {
            return str_replace('%image_file_name%', $event->imageFile->file_name, $payload);
        }
        if ($replacementString == '%profile_name%') {
            return str_replace('%profile_name%', $profile->name, $payload);
        }
        if ($replacementString == '%object_classes%') {
            $objectClasses = $profile->aiPredictions()
                ->where('detection_event_id', '=', $event->id)
                ->where('ai_prediction_detection_profile.is_masked', '=', 0)
                ->where('ai_prediction_detection_profile.is_smart_filtered', '=', 0)
                ->where('ai_prediction_detection_profile.is_size_filtered', '=', 0)
                ->pluck('object_class')
                ->sort()
                ->implode(',');

            return str_replace('%object_classes%', $objectClasses, $payload);
        }
        if ($replacementString == '%event_url%') {
            return str_replace('%event_url%', $event->eventUrl, $payload);
        }
        if ($replacementString == '%image_url%') {
            return str_replace('%image_url%', $event->imageUrl, $payload);
        }
        if ($replacementString == '%thumb_url%') {
            return str_replace('%thumb_url%', $event->thumbUrl, $payload);
        }
        if ($replacementString == '%image_download_link%') {
            return str_replace('%image_download_link%', $event->imageDownload, $payload);
        }

        throw new Exception('Unrecognized replacement string '.$replacementString);
    }

    public static function doReplacements(
        string $payload,
        DetectionEvent $event,
        DetectionProfile $profile
    ) {
        $replaced = $payload;

        foreach (array_keys(self::$availableReplacements) as $replacementString) {
            $replaced = self::doReplacement($replaced, $event, $profile, $replacementString);
        }

        return $replaced;
    }

    public static function getEventPayload(DetectionEvent $event, DetectionProfile $profile)
    {
        $predictions = $profile->aiPredictions()
            ->where('detection_event_id', '=', $event->id)
            ->get();

        $payload = [
            'detection_event' => $event->toArray(),
            'detection_profile' => $profile->toArray(),
            'predictions' => $predictions->toArray(),
        ];

        return json_encode($payload);
    }
}
