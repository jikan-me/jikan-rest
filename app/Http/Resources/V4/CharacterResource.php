<?php

namespace App\Http\Resources\V4;

use Illuminate\Http\Resources\Json\JsonResource;

class CharacterResource extends JsonResource
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
            'url' => $this->url,
            'images' => $this->image,
            'name' => $this->name,
            'nicknames' => $this->nicknames,
            'about' => $this->about,
            'member_favorites' => $this->member_favorites,
            'animeography' => $this->animeography,
            'mangaography' => $this->mangaography,
            'voice_actors' => $this->voice_actors,
        ];
    }
}