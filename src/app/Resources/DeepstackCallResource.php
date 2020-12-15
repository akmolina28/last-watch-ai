<?php

namespace App\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DeepstackCallResource extends JsonResource
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
            'input_file' => $this->input_file,
            'created_at' => $this->created_at,
            'called_at' => $this->called_at,
            'returned_at' => $this->returned_at,
            'run_time_seconds' => $this->runTimeSeconds,
            'response_json' => $this->response_json,
            'is_error' => $this->is_error,
            'detection_event_id' => $this->detection_event_id,
        ];
    }
}
