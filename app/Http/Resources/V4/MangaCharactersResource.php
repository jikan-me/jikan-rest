<?php

namespace App\Http\Resources\V4;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MangaCharactersResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     *
     *  @OA\Schema(
     *      schema="manga_characters",
     *      description="Manga Characters Resource",
     *
     *     @OA\Property(
     *          property="data",
     *          type="array",
     *
     *          @OA\Items(
     *               type="object",
     *
     *               @OA\Property(
     *                   property="character",
     *                   type="object",
     *                   ref="#/components/schemas/character_meta",
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
    /**
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return $this['characters'];
    }
}
