<?php

namespace App\Tasks;

use App\DetectionEvent;
use Illuminate\Support\Facades\Date;

class DeleteDetectionEventsTask
{
    public static function run($retentionDays)
    {
        if ($retentionDays > 0) {
            DetectionEvent::
            where('occurred_at', '<', Date::now()
                ->addDays(-1 * $retentionDays)->format('Y-m-d H:i:s'))
                ->delete();
        }
    }
}
