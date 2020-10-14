<?php

namespace App\Jobs;

use App\AiPrediction;
use App\DeepstackClientInterface;
use App\DeepstackResult;
use App\DetectionEvent;
use App\DetectionProfile;
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
     * @return void
     */
    public function handle(DeepstackClientInterface $client)
    {
        $path = Storage::path($this->event->image_file_name);

        $response = $client->detection($path);

        $this->event->deepstack_response = $response;
        $this->event->save();

        $result = new DeepstackResult($response);

        $matchedProfiles = [];

        foreach ($result->getPredictions() as $prediction) {
            $aiPrediction = AiPrediction::create([
                'object_class' => $prediction->label,
                'confidence' => $prediction->confidence,
                'x_min' => $prediction->x_min,
                'x_max' => $prediction->x_max,
                'y_min' => $prediction->y_min,
                'y_max' => $prediction->y_max,
                'detection_event_id' => $this->event->id
            ]);

            $relevantProfiles = DetectionProfile::
                whereHas('patternMatchedEvents', function ($query) {
                    $query->where('detection_event_id', '=', $this->event->id)
                        ->where('is_profile_active', '=', 1);
                })
                ->whereJsonContains('object_classes', $prediction->label)
                ->where('min_confidence', '<=', $prediction->confidence)
                ->get();

            foreach ($relevantProfiles as $profile) {
                $maskName = $profile->slug.'.png';
                $maskPath = Storage::path('masks/'.$maskName);
                $isMasked = $profile->use_mask && $aiPrediction->isMasked($maskPath);

                $objectFiltered = false;

                if (!$isMasked && $profile->use_smart_filter) {
                    $profileId = $profile->id;
                    $lastDetectionEvent = DetectionEvent::where('id', '!=', $this->event->id)
                        ->whereHas('detectionProfiles', function ($q) use ($profileId) {
                            $q->where('detection_profile_id', '=', $profileId);
                        })->latest()->first();

                    if ($lastDetectionEvent) {
                        $objectFiltered = $profile->isPredictionSmartFiltered($aiPrediction, $lastDetectionEvent);
                    }
                }

                $profile->aiPredictions()->attach($aiPrediction->id, [
                    'is_masked' => $isMasked,
                    'is_smart_filtered' => $objectFiltered
                ]);

                if (!$isMasked && !$objectFiltered) {
                    if (!in_array($profile, $matchedProfiles)) {
                        array_push($matchedProfiles, $profile);
                    }
                }
            }
        }

        foreach ($matchedProfiles as $profile) {
            $profile->load(['telegramConfigs']);
            foreach ($profile->telegramConfigs as $config) {
                ProcessTelegramJob::dispatch($this->event, $config);
            }

            foreach ($profile->webRequestConfigs as $config) {
                ProcessWebRequestJob::dispatch($this->event, $config);
            }

            foreach ($profile->folderCopyConfigs as $config) {
                ProcessFolderCopyJob::dispatch($this->event, $config, $profile);
            }

            foreach ($profile->smbCifsCopyConfigs as $config) {
                ProcessSmbCifsCopyJob::dispatch($this->event, $config, $profile);
            }
        }
    }
}
