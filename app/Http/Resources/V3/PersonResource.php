<?php

namespace App\Http\Resources\V3;

use Illuminate\Http\Resources\Json\JsonResource;

class PersonResource extends JsonResource
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
            'website_url' => $this->website_url,
            'image_url' => $this->images['jpg']['image_url'],
            'name' => $this->name,
            'given_name' => $this->given_name,
            'family_name' => $this->family_name,
            'alternate_names' => $this->alternate_names,
            'birthday' => $this->birthday,
            'member_favorites' => $this->favorites,
            'about' => $this->about,
            'voice_acting_roles' => $this->voice_acting_roles,
            'anime_staff_positions' => $this->anime_staff_positions,
            'published_manga' => $this->published_manga,
        ];
    }
}