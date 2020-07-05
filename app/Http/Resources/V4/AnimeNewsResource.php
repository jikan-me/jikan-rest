<?php

namespace App\Http\Resources\V4;

use Illuminate\Http\Resources\Json\JsonResource;

class AnimeNewsResource extends JsonResource
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
            'last_visible_page' => $this['last_visible_page'] ?? null,
            'has_next_page' => $this['has_next_page'] ?? false,
            'articles' => $this['articles']
        ];
    }
}