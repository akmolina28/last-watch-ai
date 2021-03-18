<?php

namespace App\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DetectionEventResource extends JsonResource
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
            'image_file_name' => $this->whenLoaded('imageFile') ?
                $this->imageFile->file_name : null,
            'image_file_path' => $this->whenLoaded('imageFile') ?
                $this->imageFile->getStoragePath() : null,
            'image_width' => $this->whenLoaded('imageFile') ?
                $this->imageFile->width : 0,
            'image_height' => $this->whenLoaded('imageFile') ?
                $this->imageFile->height : 0,
            'thumbnail_path' => $this->whenLoaded('imageFile') ?
                $this->imageFile->getStoragePath(true) : null,
            'occurred_at' => $this->occurred_at,
            'ai_predictions' => AiPredictionResource::collection($this->whenLoaded('aiPredictions')),
            'detection_profiles_count' => $this->detection_profiles_count,
            'pattern_matched_profiles' => DetectionProfileResource::collection($this->whenLoaded('patternMatchedProfiles')),
            'automationResults' => DetectionEventAutomationResultResource::collection(
                $this->whenLoaded('automationResults')
            ),
        ];
    }
}
