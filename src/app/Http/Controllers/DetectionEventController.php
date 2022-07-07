<?php

namespace App\Http\Controllers;

use App\DetectionEvent;
use App\DetectionProfile;
use App\Jobs\ProcessEventUploadJob;
use App\ProfileGroup;
use App\Resources\DetectionEventResource;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class DetectionEventController extends Controller
{
    public function index(Request $request)
    {
        $query = DetectionEvent::query()
            ->withCount([
                'detectionProfiles' => function ($q) {
                    $q->where('ai_prediction_detection_profile.is_relevant', '=', true);
                },
                'patternMatchedProfiles',
            ])
            ->where('is_processed', '=', 1);

        if ($request->has('profileId')) {
            $profileId = $request->get('profileId');

            if ($request->has('relevant')) {
                $query->whereHas('detectionProfiles', function ($q) use ($profileId) {
                    return $q
                        ->where('detection_profile_id', $profileId)
                        ->where('ai_prediction_detection_profile.is_relevant', '=', true);
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
                ->with('imageFile')
                ->orderByDesc('occurred_at')
                ->paginate(10)
        );
    }

    public function make(Request $request)
    {
        $occurredAt = Carbon::now();

        if ($request->has('occurred_at')) {
            try {
                $occurredAt = new Carbon($request->get('occurred_at'));
            } catch (Exception $e) {
                return response()->json(['message' => $e->getMessage()], 201);
            }
        }

        if ($request->has('image_file')) {
            $file = $request->file('image_file');

//            $client = resolve(DeepstackClientInterface::class);
//            $s = $client->detection($file->get());

            $path = $file->store('events', 'public');
            $fileName = $file->getClientOriginalName();

            $compressionSettings = [
                'compress_images' => config('app.compress_images'),
                'image_quality' => config('app.image_quality'),
            ];

            ProcessEventUploadJob::dispatch($path, $fileName, $occurredAt, $compressionSettings)
                ->onQueue('medium');
        } else {
            return response()->json(['message' => 'Missing key image_file.'], 422);
        }

        return response()->json(['message' => 'OK'], 201);
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

        $profileId = request()->get('profileId');

        return DetectionEventResource::make($event)->withNextEvents($event, $profileId);
    }

    public function viewer(Request $request)
    {
        $group_id = null;
        if ($request->has('group')) {
            $group_slug = $request->get('group');
            $group = ProfileGroup::where('slug', $group_slug)
                ->firstOrFail();
            $group_id = $group->id;
        }

        $profile_id = null;
        if ($request->has('profile')) {
            $profile_slug = $request->get('profile');
            $profile = DetectionProfile::where('slug', $profile_slug)
                ->firstOrFail();
            $profile_id = $profile->id;
        }

        if ($request->has('event')) {
            $event_id = $request->get('event');
            $event = DetectionEvent::find($event_id);
        } else {
            $event = DetectionEvent::whereHas('detectionProfiles', function ($q) use ($profile_id, $group_id) {
                $q->where('ai_prediction_detection_profile.is_relevant', '=', true);
                if ($group_id) {
                    $q->whereHas('profileGroups', function ($r) use ($group_id) {
                        return $r->where('profile_group_id', '=', $group_id);
                    });
                } elseif ($profile_id) {
                    $q->where('detection_profile_id', '=', $profile_id);
                }

                return $q;
            })->orderByDesc('occurred_at')->firstOrFail();
        }

        $event->load([
            'aiPredictions.detectionProfiles' => function ($query) {
                return $query->withTrashed();
            },
        ]);

        return DetectionEventResource::make($event)->withNextEvents($event, $profile_id, $group_id);
    }

    public function showImage(DetectionEvent $event)
    {
        return Storage::download($event->imageFile->path, $event->imageFile->file_name);
    }

    public function showLatest()
    {
        try {
            $event = DetectionEvent::whereHas('detectionProfiles', function ($q) {
                return $q->where('ai_prediction_detection_profile.is_relevant', '=', true);
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

        $next = DetectionEvent::whereHas('patternMatchedProfiles', function ($q) use ($profileId) {
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

        $prev = DetectionEvent::whereHas('patternMatchedProfiles', function ($q) use ($profileId) {
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
