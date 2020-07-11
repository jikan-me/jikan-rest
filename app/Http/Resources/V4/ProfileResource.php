<?php

namespace App\Http\Resources\V4;

use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
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
            'mal_id' => $this->mal_id,
            'username' => $this->username,
            'url' => $this->url,
            'images' => $this->images,
            'last_online' => $this->last_online,
            'gender' => $this->gender,
            'birthday' => $this->birthday,
            'location' => $this->location,
            'joined' => $this->joined,
            'anime_stats' => $this->anime_stats,
            'manga_stats' => $this->manga_stats,
            'favorites' => $this->favorites,
            'about' => $this->about,
        ];
    }
}