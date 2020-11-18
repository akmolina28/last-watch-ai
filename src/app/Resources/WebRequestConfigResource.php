<?php


namespace App\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class WebRequestConfigResource extends JsonResource
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
            'url' => $this->url,
            'is_post' => $this->is_post,
            'body_json' => $this->body_json,
            'detection_profiles' => DetectionProfileResource::collection($this->whenLoaded('detectionProfiles'))
        ];
    }
}
