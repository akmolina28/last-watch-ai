<?php

namespace App;

use Eloquent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use PhpMqtt\Client\ConnectionSettings;
use PhpMqtt\Client\Exceptions\DataTransferException;
use PhpMqtt\Client\MQTTClient;

/**
 * AiPrediction.
 *
 * @mixin Eloquent
 */
class MqttPublishConfig extends Model implements AutomationConfigInterface
{
    protected $guarded = [];

    protected $casts = [
        'is_anonymous' => 'boolean',
        'is_custom_payload' => 'boolean',
    ];

    public function detectionProfiles(): MorphToMany
    {
        return $this->morphToMany('App\DetectionProfile', 'automation_config');
    }

    public function getPayload(DetectionEvent $event, DetectionProfile $profile)
    {
        $predictions = $profile->aiPredictions()
            ->where('detection_event_id', '=', $event->id)
            ->get();

        $payload = [
            'detection_event' => $event->toArray(),
            'detection_profile' => $profile->toArray(),
            'predictions' => $predictions->toArray()
        ];

        return json_encode($payload);
    }

    public function run(DetectionEvent $event, DetectionProfile $profile): DetectionEventAutomationResult
    {
        $payload = $this->payload_json;
        if (! $this->is_custom_payload) {
            $payload = $this->getPayload($event, $profile);
        }

        $mqtt = new MQTTClient($this->server, $this->port, $this->client_id);

        if ($this->is_anonymous) {
            $mqtt->connect();
        } else {
            $connectionSettings = new ConnectionSettings();
            $mqtt->connect($this->username, $this->password, $connectionSettings, true);
        }

        $isError = false;
        $responseText = '';
        try {
            $mqtt->publish($this->topic, $payload, $this->qos);
        } catch (DataTransferException $e) {
            $isError = true;
            $responseText = $e->getMessage();
        }

        return new DetectionEventAutomationResult([
            'is_error' => $isError,
            'response_text' => $responseText,
        ]);
    }
}
