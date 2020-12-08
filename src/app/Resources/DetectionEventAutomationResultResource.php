<?php

namespace App\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DetectionEventAutomationResultResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'is_error' => $this->is_error,
            'response_text' => $this->response_text,
            'detection_event_id' => $this->detection_event_id,
            'created_at' => $this->created_at,
            'automation_config_id' => $this->automation_config_id,
            'automation_config' => AutomationConfigResource::make($this->whenLoaded('automationConfig')),
            'detection_event' => DetectionEventResource::make($this->whenLoaded('detectionEvent')),
        ];
    }
}
