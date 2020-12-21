<?php

namespace App\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MqttPublishConfigResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'server' => $this->server,
            'port' => $this->port,
            'topic' => $this->topic,
            'client_id' => $this->client_id,
            'qos' => $this->qos,
            'is_anonymous' => $this->is_anonymous,
            'username' => $this->username,
            'password' => $this->password,
            'payload_json' => $this->payload_json,
            'is_custom_payload' => $this->is_custom_payload,
            'detection_profiles' => DetectionProfileResource::collection($this->whenLoaded('detectionProfiles')),
        ];
    }
}
