<?php

namespace App;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use PhpMqtt\Client\ConnectionSettings;
use PhpMqtt\Client\Exceptions\MqttClientException;
use PhpMqtt\Client\MqttClient;

/**
 * AiPrediction.
 *
 * @mixin Eloquent
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $name
 * @property string $server
 * @property string $port
 * @property string $topic
 * @property string|null $client_id
 * @property int $qos
 * @property bool $is_anonymous
 * @property string|null $username
 * @property string|null $password
 * @property bool $is_custom_payload
 * @property string|null $payload_json
 * @property Carbon|null $deleted_at
 * @property-read Collection|DetectionProfile[] $detectionProfiles
 * @property-read int|null $detection_profiles_count
 * @method static Builder|MqttPublishConfig newModelQuery()
 * @method static Builder|MqttPublishConfig newQuery()
 * @method static \Illuminate\Database\Query\Builder|MqttPublishConfig onlyTrashed()
 * @method static Builder|MqttPublishConfig query()
 * @method static Builder|MqttPublishConfig whereClientId($value)
 * @method static Builder|MqttPublishConfig whereCreatedAt($value)
 * @method static Builder|MqttPublishConfig whereDeletedAt($value)
 * @method static Builder|MqttPublishConfig whereId($value)
 * @method static Builder|MqttPublishConfig whereIsAnonymous($value)
 * @method static Builder|MqttPublishConfig whereIsCustomPayload($value)
 * @method static Builder|MqttPublishConfig whereName($value)
 * @method static Builder|MqttPublishConfig wherePassword($value)
 * @method static Builder|MqttPublishConfig wherePayloadJson($value)
 * @method static Builder|MqttPublishConfig wherePort($value)
 * @method static Builder|MqttPublishConfig whereQos($value)
 * @method static Builder|MqttPublishConfig whereServer($value)
 * @method static Builder|MqttPublishConfig whereTopic($value)
 * @method static Builder|MqttPublishConfig whereUpdatedAt($value)
 * @method static Builder|MqttPublishConfig whereUsername($value)
 * @method static \Illuminate\Database\Query\Builder|MqttPublishConfig withTrashed()
 * @method static \Illuminate\Database\Query\Builder|MqttPublishConfig withoutTrashed()
 */
class MqttPublishConfig extends Model implements AutomationConfigInterface
{
    use SoftDeletes;

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
        $payload = $this->payload_json;

        if ($this->is_custom_payload) {
            $payload = PayloadHelper::doReplacements($payload, $event, $profile);
        }
        if (! $this->is_custom_payload) {
            $payload = PayloadHelper::getEventPayload($event, $profile);
        }

        return $payload;
    }

    public function run(DetectionEvent $event, DetectionProfile $profile): bool
    {
        $payload = $this->payload_json;

        if ($this->is_custom_payload) {
            $payload = PayloadHelper::doReplacements($payload, $event, $profile);
        }
        if (! $this->is_custom_payload) {
            $payload = PayloadHelper::getEventPayload($event, $profile);
        }

        $mqtt = new MqttClient($this->server, $this->port, $this->client_id);

        $connectionSettings = (new ConnectionSettings)
            ->setUsername($this->is_anonymous ? null : $this->username)
            ->setPassword($this->is_anonymous ? null : $this->password);

        $isError = false;
        $responseText = '';
        try {
            $mqtt->connect($connectionSettings, true);

            $mqtt->publish($this->topic, $payload, $this->qos);

            $mqtt->disconnect();
        } catch (MqttClientException $e) {
            $isError = true;
            $responseText = $e->getMessage();
        }

        return true;
    }

    protected static function booted()
    {
        static::deleted(function ($config) {
            $config->update(['name' => time().'::'.$config->name]);
        });
    }
}
