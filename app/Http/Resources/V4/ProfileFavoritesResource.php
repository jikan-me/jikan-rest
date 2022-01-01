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
     *     schema="user favorites",
     *     type="object",
     *
     *     @OA\Property(
     *          property="data",
     *          type="object",
     *          description="Favorite entries",
     *
     *          @OA\Property (
     *              property="anime",
     *              type="array",
     *              description="Favorite Anime",
     *
     *              @OA\Items (
     *                  type="object",
     *
     *                  allOf={
     *                      @OA\Schema (ref="#/components/schemas/anime meta"),
     *                      @OA\Schema (
     *                          @OA\Property (
     *                              property="type",
     *                              type="string"
     *                          ),
     *                          @OA\Property (
     *                              property="start_year",
     *                              type="integer"
     *                          ),
     *                      ),
     *                  },
     *              ),
     *          ),
     *          @OA\Property(
     *              property="manga",
     *              type="array",
     *              description="Favorite Manga",
     *
     *              @OA\Items (
     *                  type="object",
     *                  allOf={
     *                      @OA\Schema (ref="#/components/schemas/manga meta"),
     *                      @OA\Schema (
     *                          @OA\Property (
     *                              property="type",
     *                              type="string"
     *
     *                          ),
     *                          @OA\Property (
     *                              property="start_year",
     *                              type="integer"
     *
     *                          ),
     *                      ),
     *                  },
     *              ),
     *          ),
     *          @OA\Property(
     *              property="characters",
     *              type="array",
     *              description="Favorite Characters",
     *              @OA\Items (
     *                  type="object",
     *                  allOf={
     *                      @OA\Schema (ref="#/components/schemas/character meta"),
     *                      @OA\Schema (
     *                          @OA\Property (
     *                               type="object",
     *                               ref="#/components/schemas/mal_url_2",
     *                          ),
     *                      ),
     *                  },
     *              ),
     *          ),
     *          @OA\Property(
     *              property="people",
     *              type="array",
     *              description="Favorite People",
     *              @OA\Items (
     *                  type="object",
     *
     *                  @OA\Property (
     *                       type="object",
     *                       ref="#/components/schemas/character meta",
     *                  ),
     *              ),
     *          ),
     *      ),
     *  ),
     */
    public function toArray($request)
    {
        return $this->favorites;
    }
}