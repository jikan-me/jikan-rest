<?php

namespace App\Http\Resources\V3;

use Illuminate\Http\Resources\Json\JsonResource;

class PicturesResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'pictures' => $this->bcPictures($this['pictures'])
        ];
    }

    private function bcPictures($pictures) : array
    {
        foreach($pictures as &$picture) {
            $picture = [
                'large' => $picture['large_image_url'],
                'small' => $picture['image_url'],
            ];
        }

        return $pictures;
    }
}