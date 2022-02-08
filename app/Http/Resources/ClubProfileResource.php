<?php

namespace App\Http\Resources;

use App\Http\Resources\BaseResource;

class ClubProfileResource extends BaseResource
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
            'biography' => $this->biography,
            'profile_picture' => $this->profile_picture,
            'created_at' => formatTimestamp($this->created_at),
            'members' => ClubMemberResource::collection($this->whenLoaded('members')),
        ];
    }
}
