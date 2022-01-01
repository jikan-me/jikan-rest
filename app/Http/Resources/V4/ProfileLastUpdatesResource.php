<?php

namespace App\Http\Resources\V4;

use Illuminate\Http\Resources\Json\JsonResource;

class ProfileLastUpdatesResource extends JsonResource
{

    /**
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable|mixed
     *
     * @OA\Schema(
     *     schema="user updates",
     *     type="object",
     *
     *     @OA\Property(
     *          property="data",
     *          type="object",
     *
     *          @OA\Property (
     *              property="anime",
     *              type="array",
     *              description="Last updated Anime",
     *
     *              @OA\Items (
     *                  type="object",
     *
     *                  allOf={
     *                      @OA\Schema (
     *                          @OA\Property (
     *                               property="entry",
     *                               type="object",
     *                               ref="#/components/schemas/anime meta",
     *                          ),
     *                      ),
     *                      @OA\Schema (
     *                          @OA\Property (
     *                              property="score",
     *                              type="integer"
     *                          ),
     *                          @OA\Property (
     *                              property="status",
     *                              type="string"
     *                          ),
     *                          @OA\Property (
     *                              property="episodes_seen",
     *                              type="integer"
     *                          ),
     *                          @OA\Property (
     *                              property="episodes_total",
     *                              type="integer"
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
     *                      @OA\Schema (
     *                          @OA\Property (
     *                               property="entry",
     *                               type="object",
     *                               ref="#/components/schemas/manga meta",
     *                          ),
     *                      ),
     *                      @OA\Schema (
     *                          @OA\Property (
     *                              property="score",
     *                              type="integer"
     *                          ),
     *                          @OA\Property (
     *                              property="status",
     *                              type="string"
     *                          ),
     *                          @OA\Property (
     *                              property="chapters_read",
     *                              type="integer"
     *                          ),
     *                          @OA\Property (
     *                              property="chapters_total",
     *                              type="integer"
     *                          ),
     *                          @OA\Property (
     *                              property="volumes_read",
     *                              type="integer"
     *                          ),
     *                          @OA\Property (
     *                              property="volumes_total",
     *                              type="integer"
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