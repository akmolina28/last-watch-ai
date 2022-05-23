<?php

namespace App\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DetectionProfileResource extends JsonResource
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
            'slug' => $this->slug,
            'file_pattern' => $this->file_pattern,
            'object_classes' => $this->object_classes,
            'min_confidence' => $this->min_confidence,
            'use_mask' => $this->use_mask,
            'use_regex' => $this->use_regex,
            'is_negative' => $this->is_negative,
            'use_smart_filter' => $this->use_smart_filter,
            'smart_filter_precision' => $this->smart_filter_precision,
            'min_object_size' => $this->min_object_size,
            'privacy_mode' => $this->privacy_mode,
            'status' => $this->status,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'is_relevant' => $this->whenPivotLoaded('ai_prediction_detection_profile', function () {
                return $this->pivot->is_relevant;
            }),
            'is_masked' => $this->whenPivotLoaded('ai_prediction_detection_profile', function () {
                return $this->pivot->is_masked;
            }),
            'is_smart_filtered' => $this->whenPivotLoaded('ai_prediction_detection_profile', function () {
                return $this->pivot->is_smart_filtered;
            }),
            'is_size_filtered' => $this->whenPivotLoaded('ai_prediction_detection_profile', function () {
                return $this->pivot->is_size_filtered;
            }),
            'is_profile_active' => $this->whenPivotLoaded('pattern_match', function () {
                return $this->pivot->is_profile_active;
            }),
        ];
    }
}
