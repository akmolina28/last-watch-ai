<?php

namespace App\Jobs;

use App\DetectionProfile;
use App\Exceptions\WebhookRequestException;
use App\Factories\DetectionEventModelFactory;
use Illuminate\Support\Carbon;
use \Spatie\WebhookClient\ProcessWebhookJob as SpatieProcessWebhookJob;

class ProcessWebhookJob extends SpatieProcessWebhookJob
{
    /**
     * @param Carbon|null $occurred_at
     * @throws WebhookRequestException
     */
    public function handle(Carbon $occurred_at = null)
    {
        $imageFileName = $this->getImageFileNameFromRequest();

        $event = DetectionEventModelFactory::createFromImageFile($imageFileName, $occurred_at);

        $activeMatchedProfiles = $event->matchEventToProfiles(DetectionProfile::all());

        // skip AI job if no active profiles are matched
        if ($activeMatchedProfiles > 0) {
            ProcessDetectionEventJob::dispatch($event);
        }
    }

    /**
     * @return mixed
     * @throws WebhookRequestException
     */
    protected function getImageFileNameFromRequest() {

        if (array_key_exists('file', $this->webhookCall->payload)) {
            return $imageFileName = $this->webhookCall->payload['file'];
        }

        throw WebhookRequestException::fileMissingFromPayload();
    }
}
