<?php

namespace App\Http\Resources\V4;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class UserCollection extends ResourceCollection
{
    public $collects = 'App\Http\Resources\V4\ProfileResource';

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
