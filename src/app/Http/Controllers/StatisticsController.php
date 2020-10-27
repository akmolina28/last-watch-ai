<?php

namespace App\Http\Controllers;

use App\DetectionEvent;
use App\DetectionEventAutomationResult;
use App\Resources\DetectionEventAutomationResultResource;
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

        $totalErrors = DetectionEventAutomationResult::where('is_error', '=', 1)
            ->where(
                'detection_event_automation_results.created_at',
                '>',
                Date::now()->addDays(-1)->format('Y-m-d H:i:s')
            )
            ->count();

        return response()->json(['data' => [
            'relevant_events' => $relevantEvents,
            'total_events' => $totalEvents,
            'total_errors' => $totalErrors
        ]], 200);
    }

    public function errors()
    {
        $errors = DetectionEventAutomationResult::with(['automationConfig', 'detectionEvent'])
            ->where('is_error', '=', 1)
            ->where('created_at', '>', Date::now()->addDays(-1)->format('Y-m-d H:i:s'))
            ->latest()
            ->paginate(10);

        return DetectionEventAutomationResultResource::collection($errors);
    }
}
