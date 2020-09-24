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
            'image_file_name' => basename($this->image_file_name),
            'image_dimensions' => $this->image_dimensions,
            'occurred_at' => $this->occurred_at,
            'ai_predictions' => AiPredictionResource::collection($this->whenLoaded('aiPredictions')),
            'detection_profiles_count' => $this->detection_profiles_count
        ];
    }
}
