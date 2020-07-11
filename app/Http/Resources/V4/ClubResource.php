<?php

namespace App\Http\Resources\V4;

use Illuminate\Http\Resources\Json\JsonResource;

class ClubResource extends JsonResource
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
            'title' => $this->title,
            'members_count' => $this->members_count,
            'pictures_count' => $this->pictures_count,
            'category' => $this->category,
            'created' => $this->created,
            'type' => $this->type,
            'staff' => $this->staff,
            'anime_relations' => $this->anime_relations,
            'manga_relations' => $this->manga_relations,
            'character_relations' => $this->character_relations,
        ];
    }
}