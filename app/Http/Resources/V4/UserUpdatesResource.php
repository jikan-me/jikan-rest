<?php

namespace App\Http\Resources\V4;

use Illuminate\Http\Resources\Json\JsonResource;

class UserUpdatesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     *
     *  @OA\Schema(
     *      schema="anime userupdates",
     *      description="Anime User Updates Resource",
     *
     *      allOf={
     *          @OA\Schema(ref="#/components/schemas/pagination"),
     *          @OA\Schema(
     *              @OA\Property(
     *                  property="data",
     *                  type="array",
     *
     *                  @OA\Items(
     *                      type="object",
     *                      @OA\Property(
     *                          property="user",
     *                          type="object",
     *                          ref="#/components/schemas/user meta"
     *                      ),
     *                      @OA\Property(
     *                          property="score",
     *                          type="integer",
     *                          description="User Score"
     *                      ),
     *                      @OA\Property(
     *                          property="status",
     *                          type="string",
     *                          description="User list status"
     *                      ),
     *                      @OA\Property(
     *                          property="episodes_seen",
     *                          type="integer",
     *                          description="Number of episodes seen"
     *                      ),
     *                      @OA\Property(
     *                          property="episodes_total",
     *                          type="integer",
     *                          description="Total number of episodes"
     *                      ),
     *                      @OA\Property(
     *                          property="date",
     *                          type="string",
     *                          description="Last updated date ISO8601"
     *                      ),
     *                  ),
     *              ),
     *          ),
     *      },
     *  ),
     *
     *  @OA\Schema(
     *      schema="manga userupdates",
     *      description="Manga User Updates Resource",
     *
     *      allOf={
     *          @OA\Schema(ref="#/components/schemas/pagination"),
     *          @OA\Property(
     *               property="data",
     *               type="array",
     *
     *               @OA\Items(
     *                   type="object",
     *                   @OA\Property(
     *                       property="user",
     *                       type="object",
     *                       ref="#/components/schemas/user meta"
     *                   ),
     *                   @OA\Property(
     *                       property="score",
     *                       type="integer",
     *                       description="User Score"
     *                   ),
     *                   @OA\Property(
     *                       property="status",
     *                       type="string",
     *                       description="User list status"
     *                   ),
     *                   @OA\Property(
     *                       property="volumes_read",
     *                       type="integer",
     *                       description="Number of volumes read"
     *                   ),
     *                   @OA\Property(
     *                       property="volumes_total",
     *                       type="integer",
     *                       description="Total number of volumes"
     *                   ),
     *                   @OA\Property(
     *                       property="chapters_read",
     *                       type="integer",
     *                       description="Number of chapters read"
     *                   ),
     *                   @OA\Property(
     *                       property="chapters_total",
     *                       type="integer",
     *                       description="Total number of chapters"
     *                   ),
     *                   @OA\Property(
     *                       property="date",
     *                       type="string",
     *                       description="Last updated date ISO8601"
     *                   ),
     *               ),
     *          ),
     *     },
     *  )
     */
    public function toArray($request)
    {
        return $this['users'];
    }
}