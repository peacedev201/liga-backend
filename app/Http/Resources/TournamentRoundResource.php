<?php

namespace App\Http\Resources;

use App\Http\Resources\BaseResource;

class TournamentRoundResource extends BaseResource
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
            'created_at' => formatTimestamp($this->created_at),
            'matches' => TournamentRoundMatchesResource::collection($this->whenLoaded('tournamentRoundMatches')),
            'tournament' => new TournamentResource($this->whenLoaded('tournament')),
        ];
    }
}
