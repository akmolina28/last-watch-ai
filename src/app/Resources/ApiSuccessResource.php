<?php


namespace App\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ApiSuccessResource extends JsonResource
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
            'success' => $this->success
        ];
    }
}
