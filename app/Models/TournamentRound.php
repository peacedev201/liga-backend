<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TournamentRound extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'tournament_id', 'name'
    ];

    public function tournament()
    {
        return $this->belongsTo(Tournament::class);
    }

    public function tournamentRoundMatches()
    {
        return $this->hasMany(TournamentRoundMatch::class);
    }

    public static function boot()
    {
        parent::boot();

        static::deleting(function ($obj) {
            $obj->tournamentRoundMatches()->delete();
        });
    }
}
