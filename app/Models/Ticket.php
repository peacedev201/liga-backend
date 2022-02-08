<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    //
    protected $fillable = [
        'thread_id', 'player', 'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'player', 'profileable_id');
    }
}
