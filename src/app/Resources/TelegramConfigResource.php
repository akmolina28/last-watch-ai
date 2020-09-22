<?php


namespace App\Resources;


use Illuminate\Http\Resources\Json\JsonResource;

class TelegramConfigResource extends JsonResource
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
            'token' => $this->token,
            'chat_id' => $this->chat_id,
            'created_at' => $this->created_at,
            'detection_profiles' => DetectionProfileResource::collection($this->whenLoaded('detectionProfiles'))
        ];
    }
}
