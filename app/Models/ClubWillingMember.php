<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClubWillingMember extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'club_id', 'player_id', 'status',
    ];

    public function club()
    {
        return $this->belongsTo(ClubProfile::class, 'club_id');
    }

    public function player()
    {
        return $this->belongsTo(PlayerProfile::class, 'player_id');
    }
}
