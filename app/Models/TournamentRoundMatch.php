<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TournamentRoundMatch extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'tournament_round_id', 'first_player_id', 'second_player_id', 'held_date', 'held_time', 'first_player_score', 'second_player_score', 'first_player_points', 'second_player_points'
    ];

    protected $allowedFieldsInTournament = [
        'id',
        'nick_name',
        'biography'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'first_player_score' => 'float',
        'second_player_score' => 'float',
        'first_player_points' => 'float',
        'second_player_points' => 'float',
    ];

    public function tournamentRound()
    {
        return $this->belongsTo(TournamentRound::class);
    }

    public function firstPlayer()
    {
        return $this->belongsTo(PlayerProfile::class, 'first_player_id')->select($this->allowedFieldsInTournament);
    }

    public function secondPlayer()
    {
        return $this->belongsTo(PlayerProfile::class, 'second_player_id')->select($this->allowedFieldsInTournament);
    }

    public function tournament()
    {
        return $this->hasOneThrough(Tournament::class, TournamentRound::class);
    }
}
