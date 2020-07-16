<?php

namespace App\Http\Resources\V4;

use Illuminate\Http\Resources\Json\JsonResource;

class MangaCharactersResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     *
     *  @OA\Schema(
     *      schema="manga characters",
     *      description="Manga Characters Resource",
     *
     *     @OA\Property(
     *          property="data",
     *          type="array",
     *          @OA\Items(
     *               type="object",
     *
     *               @OA\Property(
     *                   property="mal_id",
     *                   type="integer",
     *                   description="MyAnimeList ID"
     *               ),
     *               @OA\Property(
     *                   property="url",
     *                   type="string",
     *                   description="MyAnimeList URL"
     *               ),
     *               @OA\Property(
     *                   property="image_url",
     *                   type="string",
     *                   description="Image URL"
     *               ),
     *               @OA\Property(
     *                   property="name",
     *                   type="string",
     *                   description="Character Name"
     *               ),
     *               @OA\Property(
     *                   property="role",
     *                   type="string",
     *                   description="Character's Role"
     *               ),
     *          ),
     *     ),
     *  )
     */
    public function toArray($request)
    {
        return $this['characters'];
    }
}