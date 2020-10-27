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
            'image_file_name' =>
                $this->whenLoaded('detectionEvent', $this->detectionEvent->image_file_name),
            'automation_config_id' =>
                $this->whenLoaded('automationConfig', $this->automationConfig->id),
            'automation_config_type' =>
                $this->whenLoaded('automationConfig', $this->automationConfig->automation_config_type),
            'created_at' => $this->created_at
        ];
    }
}
