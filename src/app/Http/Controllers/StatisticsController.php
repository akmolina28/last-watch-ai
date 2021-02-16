<?php

namespace App\Http\Controllers;

use App\DeepstackCall;
use App\DetectionEvent;
use App\DetectionEventAutomationResult;
use App\Resources\DeepstackCallResource;
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
                Date::now()->format('Y-m-d H:i:s'),
            ])->count();

        $totalEvents = DetectionEvent::whereBetween('occurred_at', [
            Date::now()->addDays(-1)->format('Y-m-d H:i:s'),
            Date::now()->format('Y-m-d H:i:s'),
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
            'total_errors' => $totalErrors,
        ]], 200);
    }

    public function isAlive()
    {
        return response()->json(['message' => 'Alive.'], 200);
    }

    public function errors()
    {
        $errors = DetectionEventAutomationResult::with([
            'automationConfig' => function ($q) {
                return $q->withTrashed();
            },
            'detectionEvent',
            'detectionEvent.imageFile'
        ])
            ->where('is_error', '=', 1)
            ->latest()
            ->paginate(10);

        return DetectionEventAutomationResultResource::collection($errors);
    }

    public function deepstackLogs()
    {
        $logs = DeepstackCall::orderByDesc('created_at')->paginate(10);

        return DeepstackCallResource::collection($logs);
    }
}
