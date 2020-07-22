<?php

namespace App\Http\Resources\V4;

use Illuminate\Http\Resources\Json\JsonResource;

class PersonVoiceResource extends JsonResource
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
            'role' => $this['role'],
            'anime' => [
                'mal_id' => $this['anime']['mal_id'],
                'url' => $this['anime']['url'],
                'image_url' => $this['anime']['image_url'],
                'name' => $this['anime']['name']
            ],
            'character' => [
                'mal_id' => $this['character']['mal_id'],
                'url' => $this['character']['url'],
                'image_url' => $this['character']['image_url'],
                'name' => $this['character']['name']
            ]
        ];
    }
}