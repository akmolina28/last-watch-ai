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
            'status' => $this->status,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'is_masked' => $this->whenPivotLoaded('ai_prediction_detection_profile', function () {
                return $this->pivot->is_masked;
            })
        ];
    }
}
