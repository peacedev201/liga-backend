<?php

namespace App\Http\Resources;

use App\Http\Resources\BaseResource;

class NewsResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'content' => $this->content,
            'picture' => $this->picture,
            'content_picture' => $this->content_picture,
            'created_at' => formatTimestamp($this->created_at),
        ];;
    }
}
