<?php

namespace App\Http\Resources\V4;

use Illuminate\Http\Resources\Json\JsonResource;

class ExternalLinksResource extends JsonResource
{

    /**
     *  @OA\Schema(
     *      schema="external_links",
     *      description="External links",
     *
     *      @OA\Property(
     *          property="data",
     *          type="array",
     *
     *          @OA\Items(
     *              type="object",
     *
     *              @OA\Property(
     *                   property="name",
     *                   type="string",
     *              ),
     *              @OA\Property(
     *                   property="url",
     *                   type="string",
     *              ),
     *          ),
     *      ),
     *  )
     */

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->external_links;
    }
}