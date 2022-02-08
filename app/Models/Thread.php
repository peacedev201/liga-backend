<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Thread extends Model
{
    //
    protected $fillable = [
        'connection1', 'connection2',
    ];

    public function messages()
    {
        return $this->hasMany(Message::class, 'thread_id');
    }
}
