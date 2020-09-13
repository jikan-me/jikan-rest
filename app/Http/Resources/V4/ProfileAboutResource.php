<?php

namespace App\Http\Resources\V4;

use Illuminate\Http\Resources\Json\JsonResource;

class ProfileAboutResource extends JsonResource
{

    public function toArray($request)
    {
        return [
            'about' => $this->about
        ];
    }
}