<?php

namespace App\Http\Resources;

use App\Models\ClubProfile;
use App\Models\PlayerProfile;
use App\Http\Resources\BaseResource;
use App\Http\Resources\ClubProfileResource;
use App\Http\Resources\PlayerProfileResource;

class UserResource extends BaseResource
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
            'type' => $this->profileable_type,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,
            'has_verified_email' => $this->hasVerifiedEmail(),
            'created_at' => formatTimestamp($this->created_at),
            'profile' => $this->when($this->relationLoaded('profileable'), function () {
                if ($this->profileable instanceof PlayerProfile) {
                    return new PlayerProfileResource($this->profileable);
                }
                if ($this->profileable instanceof ClubProfile) {
                    return new ClubProfileResource($this->profileable);
                }
            })
        ];
    }
}
