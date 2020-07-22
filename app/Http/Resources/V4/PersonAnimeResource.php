<?php

namespace App\Http\Resources\V4;

use Illuminate\Http\Resources\Json\JsonResource;

class PersonAnimeResource extends JsonResource
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
            'position' => $this['position'],
            'anime' => [
                'mal_id' => $this['anime']['mal_id'],
                'url' => $this['anime']['url'],
                'images' => $this['anime']['images'],
                'title' => $this['anime']['title']
            ]
        ];
    }
}