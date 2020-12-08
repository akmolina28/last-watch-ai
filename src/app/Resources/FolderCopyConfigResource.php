<?php

namespace App\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FolderCopyConfigResource extends JsonResource
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
            'copy_to' => $this->copy_to,
            'overwrite' => $this->overwrite,
            'detection_profiles' => DetectionProfileResource::collection($this->whenLoaded('detectionProfiles')),
        ];
    }
}
