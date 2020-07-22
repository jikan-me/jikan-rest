<?php

namespace App\Http\Resources\V4;

use Illuminate\Http\Resources\Json\JsonResource;

class PersonMangaResource extends JsonResource
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
            'manga' => [
                'mal_id' => $this['manga']['mal_id'],
                'url' => $this['manga']['url'],
                'images' => $this['manga']['images'],
                'title' => $this['manga']['title']
            ],
        ];
    }
}