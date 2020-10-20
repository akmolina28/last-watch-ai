<?php

namespace App\Http\Controllers;

use App\DetectionEvent;
use Illuminate\Support\Facades\Date;

class StatisticsController extends Controller
{
    public function index()
    {
        $relevantEvents = DetectionEvent::
            whereHas('detectionProfiles', function ($q) {
                return $q->where('ai_prediction_detection_profile.is_masked', '=', false)
                    ->where('ai_prediction_detection_profile.is_smart_filtered', '=', false);
            })
            ->whereBetween('occurred_at', [
                Date::now()->addDays(-1)->format('Y-m-d H:i:s'),
                Date::now()->format('Y-m-d H:i:s')
            ])->count();

        $totalEvents = DetectionEvent::whereBetween('occurred_at', [
            Date::now()->addDays(-1)->format('Y-m-d H:i:s'),
            Date::now()->format('Y-m-d H:i:s')
        ])->count();

        return response()->json(['data' => [
            'relevant_events' => $relevantEvents,
            'total_events' => $totalEvents
        ]], 200);
    }
}
