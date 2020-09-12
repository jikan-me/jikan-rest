<?php

namespace App\Http\Resources\V3;

use Illuminate\Http\Resources\Json\JsonResource;

class MangaCharactersResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'characters' => $this->bcCharacters($this['characters'])
        ];
    }

    private function bcCharacters($characters) : array
    {

        return $characters;
    }
}