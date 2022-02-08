<?php

namespace App\Http\Resources;

use App\Http\Resources\BaseResource;

class TournamentResource extends BaseResource
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
            'description' => $this->description,
            'html_description' => nl2br($this->description),
            'system' => $this->system,
            'icon_picture' => $this->icon_picture,
            'banner_picture' => $this->banner_picture,
            'played_games' => $this->played_games,
            'win_games' => $this->win_games,
            'lost_games' => $this->lost_games,
            'draw_games' => $this->draw_games,
            'goals' => $this->goals,
            'points' => $this->points,
            'only_for_clubs' => $this->only_for_clubs,
            'total_participants' => $this->participants_count,
            'registration_end_date_time' => $this->registration_end_date_time ? $this->registration_end_date_time->format('Y-m-d H:i:00') : null,
            'total_slots' => $this->total_slots,
            'status' => $this->status ?? 'drafted',
            'rounds' => TournamentRoundResource::collection($this->whenLoaded('rounds')),

            'participants' => PlayerProfileResource::collection($this->whenLoaded('participants')),

            'created_at' => formatTimestamp($this->created_at),
        ];
    }
}
