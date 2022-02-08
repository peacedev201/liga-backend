<?php

namespace App\Http\Resources;

use App\Http\Resources\BaseResource;

class PlayerProfileResource extends BaseResource
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
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'nick_name' => $this->nick_name,
            'full_name' => $this->full_name,
            'biography' => $this->biography,
            'postal_code' => $this->postal_code,
            'street' => $this->street,
            'city' => $this->city,
            'country' => $this->country,
            'profile_picture' => $this->profile_picture,
            'played_games' => $this->played_games,
            'win_games' => $this->win_games,
            'draw_games' => $this->draw_games,
            'lost_games' => $this->lost_games,
            'goals' => $this->goals,
            'points' => $this->points,
            'matches' => $this->matches,
            'created_at' => formatTimestamp($this->created_at),
            'tournaments' => TournamentResource::collection($this->whenLoaded('tournaments')),
            'membership' => new ClubMemberResource($this->whenLoaded('membership')),
            'willing' => new ClubWillingMemberResource($this->whenLoaded('willing')),
            'first_matches' => TournamentRoundMatchesResource::collection($this->whenLoaded('firstTournamentRoundMatches')),
            'second_matches' => TournamentRoundMatchesResource::collection($this->whenLoaded('secondTournamentRoundMatches')),
            'setting' => new PlayerSettingResource($this->whenLoaded('setting')),
        ];
    }
}
