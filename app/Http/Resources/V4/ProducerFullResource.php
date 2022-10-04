<?php

namespace App\Http\Resources\V4;

use Illuminate\Http\Resources\Json\JsonResource;

class ProducerFullResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     *
     *  @OA\Schema(
     *      schema="producer_full",
     *      description="Producers Resource",
     *      @OA\Property(
     *          property="mal_id",
     *          type="integer",
     *          description="MyAnimeList ID"
     *      ),
     *      @OA\Property(
     *          property="url",
     *          type="string",
     *          description="MyAnimeList URL"
     *      ),
     *      @OA\Property(
     *          property="titles",
     *          type="array",
     *          description="All titles",
     *          @OA\Items(
     *              type="object",
     *              ref="#/components/schemas/title",
     *          ),
     *      ),
     *     @OA\Property(
     *         property="images",
     *         type="object",
     *         ref="#/components/schemas/common_images",
     *     ),
     *      @OA\Property(
     *          property="favorites",
     *          type="integer",
     *          description="Producers's member favorites count"
     *      ),
     *      @OA\Property(
     *          property="count",
     *          type="integer",
     *          description="Producers's anime count"
     *      ),
     *     @OA\Property(
     *         property="established",
     *         type="string",
     *         description="Established Date ISO8601",
     *         nullable=true
     *     ),
     *     @OA\Property(
     *         property="about",
     *         type="string",
     *         description="About the Producer",
     *         nullable=true
     *     ),
     *
     *      @OA\Property(
     *          property="external",
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
     *  ),
     */
    public function toArray($request)
    {
        return [
            'mal_id' => $this->mal_id,
            'url' => $this->url,
            'titles' => $this->titles,
            'images' => $this->images,
            'favorites' => $this->favorites ?? null,
            'established' => $this->established ?? null,
            'about' => $this->about ?? null,
            'count' => $this->count ?? null,
            'external' => $this->external_links ?? null
        ];
    }
}
