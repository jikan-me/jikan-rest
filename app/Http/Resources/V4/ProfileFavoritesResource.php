<?php

namespace App\Http\Resources\V4;

use Illuminate\Http\Resources\Json\JsonResource;

class ProfileFavoritesResource extends JsonResource
{

    /**
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable|mixed
     *
     *
     * @OA\Schema(
     *     schema="user_favorites",
     *     type="object",
     *
     *     @OA\Property (
     *         property="anime",
     *         type="array",
     *         description="Favorite Anime",
     *
     *         @OA\Items (
     *             type="object",
     *
     *             allOf={
     *                 @OA\Schema (ref="#/components/schemas/anime_meta"),
     *                 @OA\Schema (
     *                     @OA\Property (
     *                         property="type",
     *                         type="string"
     *                     ),
     *                     @OA\Property (
     *                         property="start_year",
     *                         type="integer"
     *                     ),
     *                 ),
     *             },
     *         ),
     *     ),
     *     @OA\Property(
     *         property="manga",
     *         type="array",
     *         description="Favorite Manga",
     *
     *         @OA\Items (
     *             type="object",
     *             allOf={
     *                 @OA\Schema (ref="#/components/schemas/manga_meta"),
     *                 @OA\Schema (
     *                     @OA\Property (
     *                         property="type",
     *                         type="string"
     *
     *                     ),
     *                     @OA\Property (
     *                         property="start_year",
     *                         type="integer"
     *
     *                     ),
     *                 ),
     *             },
     *         ),
     *     ),
     *     @OA\Property(
     *         property="characters",
     *         type="array",
     *         description="Favorite Characters",
     *         @OA\Items (
     *             type="object",
     *             allOf={
     *                 @OA\Schema (ref="#/components/schemas/character_meta"),
     *                 @OA\Schema (ref="#/components/schemas/mal_url_2"),
     *             },
     *         ),
     *     ),
     *     @OA\Property(
     *         property="people",
     *         type="array",
     *         description="Favorite People",
     *         @OA\Items (
     *             ref="#/components/schemas/character_meta",
     *         ),
     *     ),
     * ),
     */
    public function toArray($request)
    {
        return $this->favorites;
    }
}
