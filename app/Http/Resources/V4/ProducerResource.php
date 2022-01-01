<?php

namespace App\Http\Resources\V4;

use Illuminate\Http\Resources\Json\JsonResource;

class ProducerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     *
     *  @OA\Schema(
     *      schema="producer",
     *      description="Producer Resource",
     *      @OA\Property(
     *          property="mal_id",
     *          type="integer",
     *          description="MyAnimeList ID"
     *      ),
     *      @OA\Property(
     *          property="name",
     *          type="string",
     *          description="Producer Name"
     *      ),
     *      @OA\Property(
     *          property="url",
     *          type="string",
     *          description="MyAnimeList URL"
     *      ),
     *      @OA\Property(
     *          property="count",
     *          type="integer",
     *          description="Producer's anime count"
     *      ),
     *  ),
     */
    public function toArray($request)
    {
        return [
            'mal_id' => $this->mal_id,
            'name' => $this->name,
            'url' => $this->url,
            'count' => $this->count
        ];
    }
}