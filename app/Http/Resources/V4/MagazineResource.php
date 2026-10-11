<?php

namespace App\Http\Resources\V4;

use App\Magazine;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Magazine */
class MagazineResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     *
     *  @OA\Schema(
     *      schema="magazine",
     *      description="Magazine Resource",
     *
     *      @OA\Property(
     *          property="mal_id",
     *          type="integer",
     *          description="MyAnimeList ID"
     *      ),
     *      @OA\Property(
     *          property="name",
     *          type="string",
     *          description="Magazine Name"
     *      ),
     *      @OA\Property(
     *          property="url",
     *          type="string",
     *          description="MyAnimeList URL"
     *      ),
     *      @OA\Property(
     *          property="count",
     *          type="integer",
     *          description="Magazine's manga count"
     *      ),
     *  ),
     */
    /**
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'mal_id' => $this->mal_id,
            'name' => $this->name,
            'url' => $this->url,
            'count' => $this->count,
        ];
    }
}
