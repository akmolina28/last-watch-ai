<?php

namespace App\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AiPredictionResource extends JsonResource
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
            'object_class' => $this->object_class,
            'confidence' => $this->confidence,
            'x_min' => $this->x_min,
            'x_max' => $this->x_max,
            'y_min' => $this->y_min,
            'y_max' => $this->y_max,
            'detection_event' => DetectionEventResource::make($this->whenLoaded('detectionEvent')),
            'detection_profiles' => DetectionProfileResource::collection($this->whenLoaded('detectionProfiles')),
        ];
    }
}
