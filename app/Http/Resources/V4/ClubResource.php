<?php

namespace App\Http\Resources\V4;

use Illuminate\Http\Resources\Json\JsonResource;

class ClubResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     *
     * @OA\Schema(
     *     schema="club",
     *     description="Club Resource",
     *
     *      @OA\Property(
     *          property="data",
     *          type="object",
     *
     *          @OA\Property(
     *              property="mal_id",
     *              type="integer",
     *              description="MyAnimeList ID"
     *          ),
     *          @OA\Property(
     *              property="name",
     *              type="string",
     *              description="Club name"
     *          ),
     *          @OA\Property(
     *              property="url",
     *              type="string",
     *              description="Club URL"
     *          ),
     *          @OA\Property(
     *              property="images",
     *              type="object",
     *              ref="#/components/schemas/common images",
     *          ),
     *          @OA\Property(
     *              property="members",
     *              type="integer",
     *              description="Number of club members"
     *          ),
     *          @OA\Property(
     *              property="category",
     *              type="string",
     *              description="Club Category",
     *              enum={"actors & artists", "anime", "characters", "cities & neighborhoods", "companies", "conventions", "games", "japan", "manga", "music", "others", "schools"}
     *          ),
     *          @OA\Property(
     *              property="created",
     *              type="string",
     *              description="Date Created ISO8601"
     *          ),
     *          @OA\Property(
     *              property="access",
     *              type="string",
     *              description="Club access",
     *              enum={"public", "private", "secret"}
     *          ),
     *     ),
     * ),
     */
    public function toArray($request)
    {
        return [
            'mal_id' => $this->mal_id,
            'url' => $this->url,
            'images' => $this->images,
            'name' => $this->name,
            'members' => $this->members,
            'category' => $this->category,
            'created' => $this->created,
            'access' => $this->access,
        ];
    }
}