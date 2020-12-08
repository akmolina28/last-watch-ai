<?php

namespace App\Http\Controllers;

use App\DetectionEvent;
use App\Resources\DetectionEventResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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
                'patternMatchedProfiles',
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
                return $query->withTrashed();
            },
            'patternMatchedProfiles' => function ($query) {
                return $query->withTrashed();
            },
            'automationResults',
        ]);

        return DetectionEventResource::make($event);
    }

    public function showLatest()
    {
        try {
            $event = DetectionEvent::whereHas('detectionProfiles', function ($q) {
                return $q->where('ai_prediction_detection_profile.is_masked', '=', false)
                    ->where('ai_prediction_detection_profile.is_smart_filtered', '=', false);
            })->orderByDesc('occurred_at')->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Model not found.'], 204);
        }

        return $this->show($event);
    }

    public function findNext(DetectionEvent $event)
    {
        $matchedProfile = $event->patternMatchedProfiles()->first();

        if (! $matchedProfile) {
            return response()->json(['message' => 'Not Found.'], 404);
        }

        $profileId = $matchedProfile->id;

        $next = DetectionEvent::
            whereHas('patternMatchedProfiles', function ($q) use ($profileId) {
                return $q->where('pattern_match.detection_profile_id', '=', $profileId);
            })
            ->where('occurred_at', '>=', $event->occurred_at)
            ->where('id', '!=', $event->id)
            ->orderBy('occurred_at')->first();

        if (! $next) {
            return response()->json(['message' => 'Not Found.'], 404);
        }

        return DetectionEventResource::make($next);
    }

    public function findPrev(DetectionEvent $event)
    {
        $matchedProfile = $event->patternMatchedProfiles()->first();

        if (! $matchedProfile) {
            return response()->json(['message' => 'Not Found.'], 404);
        }

        $profileId = $matchedProfile->id;

        $prev = DetectionEvent::
            whereHas('patternMatchedProfiles', function ($q) use ($profileId) {
                return $q->where('pattern_match.detection_profile_id', '=', $profileId);
            })
            ->where('occurred_at', '<=', $event->occurred_at)
            ->where('id', '!=', $event->id)
            ->orderByDesc('occurred_at')->first();

        if (! $prev) {
            return response()->json(['message' => 'Not Found.'], 404);
        }

        return DetectionEventResource::make($prev);
    }
}
