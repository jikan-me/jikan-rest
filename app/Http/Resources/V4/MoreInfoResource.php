<?php

namespace App\Http\Resources\V4;

use Illuminate\Http\Resources\Json\JsonResource;

class MoreInfoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     *
     *  @OA\Schema(
     *      schema="moreinfo",
     *      description="More Info Resource",
     *
     *     @OA\Property(
     *          property="data",
     *          type="object",
     *
     *          @OA\Property(
     *              property="moreinfo",
     *              type="string",
     *              description="Additional information on the entry"
     *          ),
     *     ),
     *  )
     */
    public function toArray($request)
    {
        return [
            'moreinfo' => $this['moreinfo']
        ];
    }
}