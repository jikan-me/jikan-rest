<?php

namespace App\Http\Resources\V4;

use App\Profile;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Profile */
class ProfileLastUpdatesResource extends JsonResource
{
    /**
     * @param  Request  $request
     * @return array|Arrayable|\JsonSerializable|mixed
     *
     * @OA\Schema(
     *     schema="user_updates",
     *     type="object",
     *
     *     @OA\Property(
     *          property="data",
     *          type="object",
     *          @OA\Property (
     *              property="anime",
     *              type="array",
     *              description="Last updated Anime",
     *
     *              @OA\Items (
     *                  type="object",
     *
     *                  allOf={
     *
     *                      @OA\Schema (
     *
     *                          @OA\Property (
     *                               property="entry",
     *                               type="object",
     *                               ref="#/components/schemas/anime_meta",
     *                          ),
     *                      ),
     *
     *                      @OA\Schema (
     *
     *                          @OA\Property (
     *                              property="score",
     *                              type="integer",
     *                              nullable=true
     *                          ),
     *                          @OA\Property (
     *                              property="status",
     *                              type="string"
     *                          ),
     *                          @OA\Property (
     *                              property="episodes_seen",
     *                              type="integer",
     *                              nullable=true
     *                          ),
     *                          @OA\Property (
     *                              property="episodes_total",
     *                              type="integer",
     *                              nullable=true
     *                          ),
     *                          @OA\Property (
     *                              property="date",
     *                              description="ISO8601 format",
     *                              type="string"
     *                          ),
     *                      ),
     *                  },
     *              ),
     *          ),
     *          @OA\Property (
     *              property="manga",
     *              type="array",
     *              description="Last updated Manga",
     *
     *              @OA\Items (
     *                  type="object",
     *
     *                  allOf={
     *
     *                      @OA\Schema (
     *
     *                          @OA\Property (
     *                               property="entry",
     *                               type="object",
     *                               ref="#/components/schemas/manga_meta",
     *                          ),
     *                      ),
     *
     *                      @OA\Schema (
     *
     *                          @OA\Property (
     *                              property="score",
     *                              type="integer",
     *                              nullable=true
     *                          ),
     *                          @OA\Property (
     *                              property="status",
     *                              type="string"
     *                          ),
     *                          @OA\Property (
     *                              property="chapters_read",
     *                              type="integer",
     *                              nullable=true
     *                          ),
     *                          @OA\Property (
     *                              property="chapters_total",
     *                              type="integer",
     *                              nullable=true
     *                          ),
     *                          @OA\Property (
     *                              property="volumes_read",
     *                              type="integer",
     *                              nullable=true
     *                          ),
     *                          @OA\Property (
     *                              property="volumes_total",
     *                              type="integer",
     *                              nullable=true
     *                          ),
     *                          @OA\Property (
     *                              property="date",
     *                              description="ISO8601 format",
     *                              type="string"
     *                          ),
     *                      ),
     *                  },
     *              ),
     *          ),
     *      ),
     *  ),
     */
    public function toArray($request)
    {
        return $this->last_updates;
    }
}
