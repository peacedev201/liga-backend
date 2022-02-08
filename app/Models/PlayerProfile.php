<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlayerProfile extends Model
{
    /**
     * The disk to be use for avatar.
     *
     * @var string
     */
    public $disk = 'public';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name', 'last_name', 'nick_name', 'biography', 'postal_code', 'optin_marketing', 'street', 'city', 'country', 'avatar'
    ];

    /**
     * The reslationships that should be eagar load.
     *
     * @var array
     */
    protected $with = ['setting'];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['full_name', 'profile_picture'];

    /**
     * Set the avatar of the user.
     *
     * @param $value
     * @return void
     */
    public function setAvatarAttribute($value)
    {
        if ($value) {
            $this->deleteAvatar();
            $value = $value->store('uploads/players', $this->disk);
            $this->attributes['avatar'] = $value;
        }
    }

    /**
     * Get the full name of the user.
     *
     * @return string
     */
    public function getFullNameAttribute()
    {
        $full_name = $this->attributes['nick_name'];
        $first_name = $this->first_name;
        if ($this->setting) {
            switch ($this->setting->name) {
                case 'first_nick':
                    $full_name = $first_name . " '" . $this->nick_name. "'";
                    break;
                case 'last_nick':
                    $full_name = "{$this->last_name} '{$this->nick_name}'";
                    break;
                case 'first_last_nick':
                    $full_name = "{$this->first_name} '{$this->nick_name}' {$this->last_name}";
                    break;
            }
        }
        return $full_name;
    }

    /**
     * Get the profile picture of the user.
     *
     * @return string
     */
    public function getProfilePictureAttribute()
    {
        if ($this->avatar && \Storage::disk($this->disk)->exists($this->avatar)) {
            return \Storage::disk($this->disk)->url($this->avatar);
        }
        return 'https://placehold.it/100/005FFB/ffffff/&text=' . ucfirst($this->nick_name[0]);
    }

    /**
     * Get the matches of the user.
     *
     * @return string
     */
    public function getMatchesAttribute()
    {
        return $this->firstTournamentRoundMatches->merge($this->secondTournamentRoundMatches);
    }

    /**
     * Delete the profile picture of the user.
     *
     * @return void
     */
    public function deleteAvatar()
    {
        if ($this->avatar && \Storage::disk($this->disk)->exists($this->avatar)) {
            \Storage::disk($this->disk)->delete($this->avatar);
        }
    }

    public function user()
    {
        return $this->hasOne(User::class);
    }

    public function membership()
    {
        return $this->hasOne(ClubMember::class, 'player_id');
    }

    public function willing()
    {
        return $this->hasOne(ClubWillingMember::class, 'player_id');
    }

    public function tournaments()
    {
        return $this->belongsToMany(Tournament::class, 'tournament_participants', 'player_id', 'tournament_id');
    }

    public function firstTournamentRoundMatches()
    {
        return $this->hasMany(TournamentRoundMatch::class, 'first_player_id');
    }

    public function secondTournamentRoundMatches()
    {
        return $this->hasMany(TournamentRoundMatch::class, 'second_player_id');
    }

    public function setting()
    {
        return $this->hasOne(PlayerSetting::class, 'player_id');
    }

    public static function boot()
    {
        parent::boot();

        static::deleted(function ($obj) {
            $obj->user()->delete();
            $obj->setting()->delete();
            $obj->club()->delete();
            $obj->tournaments()->detach();
            $obj->firstTournamentRoundMatches()->detach();
            $obj->secondTournamentRoundMatches()->detach();
            $obj->deleteAvatar();
        });
    }
}
