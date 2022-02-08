<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Friends extends Model
{
    //
    protected $fillable = [
        'player1', 'player2', 'status',
    ];

}
