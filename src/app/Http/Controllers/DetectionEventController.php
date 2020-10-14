<?php

namespace App\Http\Controllers;

use App\DetectionEvent;
use App\Resources\DetectionEventResource;
use Illuminate\Http\Request;

class DetectionEventController extends Controller
{
    public function index(Request $request)
    {
        $query = DetectionEvent::query()
            ->withCount([
                'detectionProfiles' => function ($q) {
                    $q->where('ai_prediction_detection_profile.is_masked', '=', false)
                        ->where('ai_prediction_detection_profile.is_smart_filtered', '=', false);
                },
                'patternMatchedProfiles'
            ]);

        if ($request->has('profileId')) {
            $profileId = $request->get('profileId');

            if ($request->has('relevant')) {
                $query->whereHas('detectionProfiles', function ($q) use ($profileId) {
                    return $q
                        ->where('detection_profile_id', $profileId)
                        ->where('ai_prediction_detection_profile.is_masked', '=', false)
                        ->where('ai_prediction_detection_profile.is_smart_filtered', '=', false);
                });
            } else {
                $query->whereHas('patternMatchedProfiles', function ($q) use ($profileId) {
                    return $q->where('detection_profile_id', $profileId);
                });
            }
        } elseif ($request->has('relevant')) {
            $query->having('detection_profiles_count', '>', 0);
        }

        return DetectionEventResource::collection(
            $query
                ->orderByDesc('occurred_at')
                ->paginate(10)
        );
    }

    public function show(DetectionEvent $event)
    {
        $event->load([
            'aiPredictions.detectionProfiles' => function ($query) {
                $query->withTrashed();
            },
            'patternMatchedProfiles' => function ($query) {
                $query->withTrashed();
            }
        ]);
        return DetectionEventResource::make($event);
    }
}
