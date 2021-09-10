<?php

namespace App\Http\Resources\V4;

use Illuminate\Http\Resources\Json\JsonResource;

class TrendsResource extends JsonResource
{

    public function toArray($request)
    {
        return [
            'url' => $this['_id'],
            'count' => $this['count'],
        ];
    }
}