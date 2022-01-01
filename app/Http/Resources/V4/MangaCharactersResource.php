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
     *                   property="character",
     *                   type="object",
     *                   ref="#/components/schemas/character meta",
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