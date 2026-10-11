<?php

namespace App\Http\Resources\V4;

use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Support\CachedData */
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
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return $this->external_links;
    }
}