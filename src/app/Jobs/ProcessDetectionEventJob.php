<?php

namespace App\Jobs;

use App\AiPrediction;
use App\AutomationConfig;
use App\DeepstackClientInterface;
use App\DeepstackResult;
use App\DetectionEvent;
use App\DetectionEventAutomationResult;
use App\DetectionProfile;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ProcessDetectionEventJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public DetectionEvent $event;

    /**
     * Create a new job instance.
     *
     * @param  DetectionEvent  $event
     * @return void
     */
    public function __construct(DetectionEvent $event)
    {
        $this->event = $event;
    }

    /**
     * Execute the job.
     *
     * @param DeepstackClientInterface $client
     * @return void
     */
    public function handle(DeepstackClientInterface $client)
    {
        $path = Storage::path($this->event->image_file_name);

        $response = $client->detection($path);

        $this->event->deepstack_response = $response;
        $this->event->save();

        $result = new DeepstackResult($response);

        $relevantProfiles = [];

        foreach ($result->getPredictions() as $prediction) {
            $aiPrediction = AiPrediction::create([
                'object_class' => $prediction->label,
                'confidence' => $prediction->confidence,
                'x_min' => $prediction->x_min,
                'x_max' => $prediction->x_max,
                'y_min' => $prediction->y_min,
                'y_max' => $prediction->y_max,
                'detection_event_id' => $this->event->id,
            ]);

            $matchedProfiles = DetectionProfile::
                whereHas('patternMatchedEvents', function ($query) {
                    $query->where('detection_event_id', '=', $this->event->id)
                        ->where('is_profile_active', '=', 1);
                })
                ->whereJsonContains('object_classes', $prediction->label)
                ->where('min_confidence', '<=', $prediction->confidence)
                ->get();

            foreach ($matchedProfiles as $profile) {
                $maskName = $profile->slug.'.png';
                $maskPath = Storage::path('masks/'.$maskName);
                $isMasked = $profile->use_mask && $aiPrediction->isMasked($maskPath);

                $objectFiltered = false;

                if (! $isMasked && $profile->use_smart_filter) {
                    $profileId = $profile->id;
                    $lastDetectionEvent = DetectionEvent::where('id', '!=', $this->event->id)
                        ->whereHas('detectionProfiles', function ($q) use ($profileId) {
                            $q->where('detection_profile_id', '=', $profileId)
                                ->where('ai_prediction_detection_profile.is_masked', '=', false);
                        })->latest()->first();

                    if ($lastDetectionEvent) {
                        $objectFiltered = $profile->isPredictionSmartFiltered($aiPrediction, $lastDetectionEvent);
                    }
                }

                $profile->aiPredictions()->attach($aiPrediction->id, [
                    'is_masked' => $isMasked,
                    'is_smart_filtered' => $objectFiltered,
                ]);

                if (! $isMasked && ! $objectFiltered && ! $profile->is_negative) {
                    if (! in_array($profile, $relevantProfiles)) {
                        array_push($relevantProfiles, $profile);
                    }
                }
            }
        }

        // profiles with negative flag set which didn't have any relevant objects
        $negativeProfiles = DetectionProfile
            ::where('is_negative', '=', 1)
            ->whereHas('patternMatchedEvents', function ($query) {
                $query
                    ->where('detection_event_id', '=', $this->event->id)
                    ->where('is_profile_active', '=', 1);
            })
            ->whereDoesntHave('detectionEvents', function ($query) {
                $query
                    ->where('detection_events.id', '=', $this->event->id)
                    ->where('ai_prediction_detection_profile.is_masked', '=', 0)
                    ->where('ai_prediction_detection_profile.is_smart_filtered', '=', 0);
            })
            ->get();

        foreach ($negativeProfiles as $profile) {
            array_push($relevantProfiles, $profile);
        }

        /* @var $profile DetectionProfile */
        foreach ($relevantProfiles as $profile) {
            /* @var $automation AutomationConfig */
            foreach ($profile->automations as $automation) {
                try {
                    $result = $automation->run($this->event, $profile);
                } catch (Exception $exception) {
                    $result = new DetectionEventAutomationResult([
                        'is_error' => 1,
                        'response_text' => $exception->getMessage(),
                    ]);
                }

                $result->detection_event_id = $this->event->id;
                $result->automation_config_id = $automation->id;
                $result->save();
            }
        }
    }
}
