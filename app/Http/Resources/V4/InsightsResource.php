<?php

namespace App\Http\Resources\V4;

use Illuminate\Http\Resources\Json\JsonResource;

class InsightsResource extends JsonResource
{

    public function toArray($request)
    {
        return [
            'timestamp' => $this['timestamp'],
            'url' => $this['url'],
        ];
    }
}