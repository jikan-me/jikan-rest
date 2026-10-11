<?php

namespace App\Http\Resources\V4;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class UserProfileAnimeListCollection extends ResourceCollection
{
    /**
     * The resource that this resource collects.
     */
    public $collects = 'App\Http\Resources\V4\UserProfileAnimeListResource';

    /**
     * Transform the resource collection into an array.
     *
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'data' => $this->collection,
        ];
    }
}
