<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use App\Notifications\User\ResetPasswordNotification;
use App\Notifications\User\VerifyEmailNotification;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'profileable_type', 'profileable_id', 'first_name', 'last_name', 'nick_name', 'biography', 'postal_code','optin_marketing', 'street', 'country', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The reslationships that should be eagar load.
     *
     * @var array
     */
    protected $with = ['profileable'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmailNotification());
    }

    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = strtolower($value);
    }

    /**
     * Get the owning profileable model.
     */
    public function profileable()
    {
        return $this->morphTo();
    }

    public static function boot()
    {
        parent::boot();

        static::deleted(function ($obj) {
            $obj->profileable()->delete();
        });
    }

    // public function tickets()
    // {
    //     return $this->hasMany(Ticket::class, 'player');
    // }
}
