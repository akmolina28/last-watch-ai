<?php

namespace App\Tasks;

use App\DetectionEvent;
use App\Jobs\DeleteEventImageJob;
use Illuminate\Support\Facades\Date;

class DeleteDetectionEventsTask
{
    public static function run($retentionDays)
    {
        if ($retentionDays > 0) {
            $deleteEvents = DetectionEvent::where('occurred_at', '<', Date::now()
                    ->addDays(-1 * $retentionDays)->format('Y-m-d H:i:s'))
                    ->cursor();

            foreach ($deleteEvents as $event) {
                if ($event->imageFile) {
                    DeleteEventImageJob::dispatch($event->imageFile)
                        ->onQueue('low')
                        // delay so that all events are deleted before trying to delete images
                        ->delay(now()->addMinutes(5));
                }
                $event->delete();
            }
        }
    }
}
