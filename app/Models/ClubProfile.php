<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClubProfile extends Model
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
        'name', 'biography', 'avatar'
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['profile_picture'];

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
            $value = $value->store('uploads/clubs', $this->disk);
            $this->attributes['avatar'] = $value;
        }
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
        return 'https://placehold.it/100/005FFB/ffffff/&text=' . ucfirst($this->name[0]);
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

    public function members()
    {
        return $this->hasMany(ClubMember::class, 'club_id');
    }

    public static function boot()
    {
        parent::boot();

        static::deleted(function ($obj) {
            $obj->user()->delete();
            $obj->members()->delete();
            $obj->deleteAvatar();
        });
    }
}
