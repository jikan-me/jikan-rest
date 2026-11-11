<?php

namespace App\Http\Resources\V4;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CharacterSeiyuuResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'language' => $this['language'],
            'person' => [
                'mal_id' => $this['person']['mal_id'],
                'url' => $this['person']['url'],
                'images' => $this['person']['images'],
                'name' => $this['person']['name'],
            ],
        ];
    }
}
