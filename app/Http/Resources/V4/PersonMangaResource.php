<?php

namespace App\Http\Resources\V4;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PersonMangaResource extends JsonResource
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
            'position' => $this['position'],
            'manga' => [
                'mal_id' => $this['manga']['mal_id'],
                'url' => $this['manga']['url'],
                'images' => $this['manga']['images'],
                'title' => $this['manga']['title'],
            ],
        ];
    }
}
