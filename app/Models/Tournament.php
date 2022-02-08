<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tournament extends Model
{
    /**
     * The disk to be use for images.
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
        'name', 'icon_image', 'banner_image', 'system', 'description', 'only_for_clubs', 'registration_end_date_time', 'total_slots', 'status'
    ];

    protected $casts = [
        'registration_end_date_time' => 'datetime:Y-m-d H:i:00',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['icon_picture', 'banner_picture'];

    /**
     * Set the icon image of the tournament.
     *
     * @param $value
     * @return void
     */
    public function setIconImageAttribute($value)
    {
        $this->deleteIconImage();
        $value = $value->store('uploads/tournaments/icons', $this->disk);
        $this->attributes['icon_image'] = $value;
    }

    /**
     * Get the icon picture of the tournament.
     *
     * @return string
     */
    public function getIconPictureAttribute()
    {
        return \Storage::disk($this->disk)->url($this->icon_image);
    }

    /**
     * Delete the icon image of the tournament.
     *
     * @return void
     */
    public function deleteIconImage()
    {
        if ($this->icon_image && \Storage::disk($this->disk)->exists($this->icon_image)) {
            \Storage::disk($this->disk)->delete($this->icon_image);
        }
    }

    /**
     * Set the banner image of the tournament.
     *
     * @param $value
     * @return void
     */
    public function setBannerImageAttribute($value)
    {
        $this->deleteBannerImage();
        $value = $value->store('uploads/tournaments/banners', $this->disk);
        $this->attributes['banner_image'] = $value;
    }

    /**
     * Get the banner picture of the tournament.
     *
     * @return string
     */
    public function getBannerPictureAttribute()
    {
        return \Storage::disk($this->disk)->url($this->banner_image);
    }

    /**
     * Delete the banner image of the tournament.
     *
     * @return void
     */
    public function deleteBannerImage()
    {
        if ($this->banner_image && \Storage::disk($this->disk)->exists($this->banner_image)) {
            \Storage::disk($this->disk)->delete($this->banner_image);
        }
    }

    public function setTotalSlotsAttribute($value)
    {
        $this->attributes['total_slots'] = (string) $value;
    }

    public function setOnlyForClubsAttribute($value)
    {
        $this->attributes['only_for_clubs'] = (boolean) $value;
    }

    public function rounds()
    {
        return $this->hasMany(TournamentRound::class);
    }

    public function matches()
    {
        return $this->hasManyThrough(TournamentRoundMatch::class, TournamentRound::class);
    }

    public function participants()
    {
        return $this->belongsToMany(PlayerProfile::class, 'tournament_participants', 'tournament_id', 'player_id');
    }

    public static function boot()
    {
        parent::boot();

        static::deleting(function ($obj) {
            foreach ($obj->rounds as $round) {
                $round->delete();
            }
            $obj->participants()->detach();
            $obj->deleteIconImage();
            $obj->deleteBannerImage();
        });
    }
}
