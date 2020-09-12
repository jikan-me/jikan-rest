<?php

namespace App\Http\Resources\V3;

use Illuminate\Http\Resources\Json\JsonResource;

class MoreInfoResource extends JsonResource
{

    public function toArray($request)
    {
        return [
            'moreinfo' => $this['moreinfo']
        ];
    }
}