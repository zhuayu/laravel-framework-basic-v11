<?php

namespace App\Http\Resources\Api\Web\Notification;

use Illuminate\Http\Resources\Json\JsonResource;

class NotificationIndexResource extends JsonResource
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
            "id" => $this->id,
            "created_at" => $this->created_at->toDateTimeString(),
            "data" => $this->data,
            "read_at" => $this->read_at
        ];
    }
}
