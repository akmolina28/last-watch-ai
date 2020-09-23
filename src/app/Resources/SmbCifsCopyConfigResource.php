<?php


namespace App\Resources;


use Illuminate\Http\Resources\Json\JsonResource;

class SmbCifsCopyConfigResource extends JsonResource
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
            'servicename' => $this->servicename,
            'user' => $this->user,
            'password' => $this->password,
            'remote_dest' => $this->remote_dest,
            'overwrite' => $this->overwrite,
            'detection_profiles' => DetectionProfileResource::collection($this->whenLoaded('detectionProfiles'))
        ];
    }
}
