<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class News extends Model
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
        'title', 'slug', 'description', 'content', 'image', 'content_image'
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['picture', 'content_picture'];

    /**
     * Set the slug of the news.
     *
     * @param $value
     * @return void
     */
    public function setTitleAttribute($value)
    {
        $this->attributes['title'] = $value;
        $this->attributes['slug'] = \Str::slug($value, '-');
    }

    /**
     * Set the image of the news.
     *
     * @param $value
     * @return void
     */
    public function setImageAttribute($value)
    {
        $this->deleteImage();
        $value = $value->store('uploads/news', $this->disk);
        $this->attributes['image'] = $value;
    }

    /**
     * Get the picture of the news.
     *
     * @return string
     */
    public function getPictureAttribute()
    {
        return \Storage::disk($this->disk)->url($this->image);
    }

    /**
     * Delete the image of the news.
     *
     * @return void
     */
    public function deleteImage()
    {
        if ($this->image && \Storage::disk($this->disk)->exists($this->image)) {
            \Storage::disk($this->disk)->delete($this->image);
        }
    }

    /**
     * Set the content image of the news.
     *
     * @param $value
     * @return void
     */
    public function setContentImageAttribute($value)
    {
        $this->deleteContentImage();
        $value = $value->store('uploads/news', $this->disk);
        $this->attributes['content_image'] = $value;
    }

    /**
     * Get the content picture of the news.
     *
     * @return string
     */
    public function getContentPictureAttribute()
    {
        return \Storage::disk($this->disk)->url($this->content_image);
    }

    /**
     * Delete the content image of the news.
     *
     * @return void
     */
    public function deleteContentImage()
    {
        if ($this->content_image && \Storage::disk($this->disk)->exists($this->content_image)) {
            \Storage::disk($this->disk)->delete($this->content_image);
        }
    }

    public static function boot()
    {
        parent::boot();

        static::deleted(function ($obj) {
            $obj->deleteImage();
            $obj->deleteContentImage();
        });
    }
}
