<?php

namespace App\Http\Resources\V3;

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
            'image_url' => $this->images['jpg']['image_url'],
            'name' => $this->name,
            'nicknames' => $this->nicknames,
            'favorites' => $this->favorites,
            'about' => $this->about,
            'animeography' => $this->animeography,
            'mangaography' => $this->mangaography,
            'voice_actors' => $this->voice_actors
        ];
    }
}