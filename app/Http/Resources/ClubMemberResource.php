<?php

namespace App\Http\Resources;

use App\Http\Resources\BaseResource;

class ClubMemberResource extends BaseResource
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
            'status' => $this->status,
            'player' => new PlayerProfileResource($this->whenLoaded('player')),
            'club' => new ClubProfileResource($this->whenLoaded('club')),
            'created_at' => formatTimestamp($this->created_at),
        ];
    }
}
