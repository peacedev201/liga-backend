<?php

namespace App\Http\Resources;

use App\Http\Resources\BaseResource;

class TournamentRoundMatchesResource extends BaseResource
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
            'first_player_id' => $this->first_player_id,
            'second_player_id' => $this->second_player_id,
            'held_date' => $this->held_date,
            'held_time' => $this->held_time,
            'first_player_score' => $this->first_player_score,
            'second_player_score' => $this->second_player_score,
            'first_player_points' => $this->first_player_points,
            'second_player_points' => $this->second_player_points,
            'first_player' => new PlayerProfileResource($this->whenLoaded('firstPlayer')),
            'second_player' => new PlayerProfileResource($this->whenLoaded('secondPlayer')),
            'tournament_round' => new TournamentRoundResource($this->whenLoaded('tournamentRound')),
            'created_at' => formatTimestamp($this->created_at),
        ];
    }
}
