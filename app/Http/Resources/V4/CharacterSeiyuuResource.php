<?php

namespace App\Http\Resources\V4;

use Illuminate\Http\Resources\Json\JsonResource;

class CharacterSeiyuuResource extends JsonResource
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
            'language' => $this['language'],
            'person' => [
                'mal_id' => $this['person']['mal_id'],
                'url' => $this['person']['url'],
                'images' => $this['person']['images'],
                'name' => $this['person']['name']
            ],
        ];
    }
}