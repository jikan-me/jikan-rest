<?php

namespace App\Http\Resources\V4;

use Illuminate\Http\Resources\Json\JsonResource;

class ProfileHistoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     *
     * @OA\Schema(
     *     schema="users",
     *     type="object",
     *
     *     @OA\Property(
     *          property="data",
     *          type="array",
     *          @OA\Items(
     *              type="object",
     *              anyOf={
     *              @OA\Schema(ref="#/components/schemas/history anime"),
     *              @OA\Schema(ref="#/components/schemas/history manga"),
     *              }
     *          ),
     *      ),
     *  ),
     * @OA\Schema(
     *     schema="history anime",
     *     type="object",
     *      @OA\Property(
     *          property="mal_id",
     *          type="integer",
     *          description="MyAnimeList ID"
     *      ),
     *      @OA\Property(
     *          property="username",
     *          type="string",
     *          description="MyAnimeList Username"
     *      ),
     *      @OA\Property(
     *          property="url",
     *          type="string",
     *          description="MyAnimeList URL"
     *      ),
     *       @OA\Property(
     *           property="anime",
     *           type="object",
     *           description="Anime Meta",
     *           ref="#/components/schemas/mal_url"
     *       ),
     *      @OA\Property(
     *          property="increment",
     *          type="integer",
     *          description="Number of episodes watched"
     *      ),
     * ),
     * @OA\Schema(
     *     schema="history manga",
     *     type="object",
     *      @OA\Property(
     *          property="mal_id",
     *          type="integer",
     *          description="MyAnimeList ID"
     *      ),
     *      @OA\Property(
     *          property="username",
     *          type="string",
     *          description="MyAnimeList Username"
     *      ),
     *      @OA\Property(
     *          property="url",
     *          type="string",
     *          description="MyAnimeList URL"
     *      ),
     *       @OA\Property(
     *           property="manga",
     *           type="object",
     *           description="Manga Meta",
     *           ref="#/components/schemas/mal_url"
     *       ),
     *      @OA\Property(
     *          property="increment",
     *          type="integer",
     *          description="Number of chapters read"
     *      ),
     * ),
     */
    public function toArray($request)
    {
        return $this['history'];
    }
}