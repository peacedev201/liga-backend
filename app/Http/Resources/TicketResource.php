<?php

namespace App\Http\Resources;

use App\Http\Resources\BaseResource;

class TicketResource extends BaseResource
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
            'thread_id' => $this->thread_id,
            'status' => $this->status,
            'player' => $this->player,
            'user' => new UserResource($this->whenLoaded('user')),
            'created_at' => formatTimestamp($this->created_at),
        ];
    }
}

