<?php

namespace App\Http\Resources\V4;

use Illuminate\Http\Resources\Json\JsonResource;

class ProfileFullResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     *
     *
     * @OA\Schema(
     *     schema="user_profile_full",
     *     type="object",
     *
     *      @OA\Property(
     *          property="mal_id",
     *          type="integer",
     *          description="MyAnimeList ID",
     *          nullable=true
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
     *           property="images",
     *           type="object",
     *           ref="#/components/schemas/user_images"
     *       ),
     *      @OA\Property(
     *          property="last_online",
     *          type="string",
     *          description="Last Online Date ISO8601",
     *          nullable=true
     *      ),
     *      @OA\Property(
     *          property="gender",
     *          type="string",
     *          description="User Gender",
     *          nullable=true
     *      ),
     *      @OA\Property(
     *          property="birthday",
     *          type="string",
     *          description="Birthday Date ISO8601",
     *          nullable=true
     *      ),
     *      @OA\Property(
     *          property="location",
     *          type="string",
     *          description="Location",
     *          nullable=true
     *      ),
     *      @OA\Property(
     *          property="joined",
     *          type="string",
     *          description="Joined Date ISO8601",
     *          nullable=true
     *      ),
     *     @OA\Property(
     *          property="statistics",
     *          type="object",
     *          @OA\Property(
     *              property="anime",
     *              type="object",
     *              description="Anime Statistics",
     *              @OA\Property(
     *                  property="days_watched",
     *                  type="number",
     *                  format="float",
     *                  description="Number of days spent watching Anime"
     *              ),
     *              @OA\Property(
     *                  property="mean_score",
     *                  type="number",
     *                  format="float",
     *                  description="Mean Score"
     *              ),
     *              @OA\Property(
     *                  property="watching",
     *                  type="integer",
     *                  description="Anime Watching"
     *              ),
     *              @OA\Property(
     *                  property="completed",
     *                  type="integer",
     *                  description="Anime Completed"
     *              ),
     *              @OA\Property(
     *                  property="on_hold",
     *                  type="integer",
     *                  description="Anime On-Hold"
     *              ),
     *              @OA\Property(
     *                  property="dropped",
     *                  type="integer",
     *                  description="Anime Dropped"
     *              ),
     *              @OA\Property(
     *                  property="plan_to_watch",
     *                  type="integer",
     *                  description="Anime Planned to Watch"
     *              ),
     *              @OA\Property(
     *                  property="total_entries",
     *                  type="integer",
     *                  description="Total Anime entries on User list"
     *              ),
     *              @OA\Property(
     *                  property="rewatched",
     *                  type="integer",
     *                  description="Anime re-watched"
     *              ),
     *              @OA\Property(
     *                  property="episodes_watched",
     *                  type="integer",
     *                  description="Number of Anime Episodes Watched"
     *              ),
     *          ),
     *          @OA\Property(
     *              property="manga",
     *              type="object",
     *              description="Manga Statistics",
     *              @OA\Property(
     *                  property="days_read",
     *                  type="number",
     *                  format="float",
     *                  description="Number of days spent reading Manga"
     *              ),
     *              @OA\Property(
     *                  property="mean_score",
     *                  type="number",
     *                  format="float",
     *                  description="Mean Score"
     *              ),
     *              @OA\Property(
     *                  property="reading",
     *                  type="integer",
     *                  description="Manga Reading"
     *              ),
     *              @OA\Property(
     *                  property="completed",
     *                  type="integer",
     *                  description="Manga Completed"
     *              ),
     *              @OA\Property(
     *                  property="on_hold",
     *                  type="integer",
     *                  description="Manga On-Hold"
     *              ),
     *              @OA\Property(
     *                  property="dropped",
     *                  type="integer",
     *                  description="Manga Dropped"
     *              ),
     *              @OA\Property(
     *                  property="plan_to_read",
     *                  type="integer",
     *                  description="Manga Planned to Read"
     *              ),
     *              @OA\Property(
     *                  property="total_entries",
     *                  type="integer",
     *                  description="Total Manga entries on User list"
     *              ),
     *              @OA\Property(
     *                  property="reread",
     *                  type="integer",
     *                  description="Manga re-read"
     *              ),
     *              @OA\Property(
     *                  property="chapters_read",
     *                  type="integer",
     *                  description="Number of Manga Chapters Read"
     *              ),
     *              @OA\Property(
     *                  property="volumes_read",
     *                  type="integer",
     *                  description="Number of Manga Volumes Read"
     *              ),
     *          ),
     *      ),
     *      @OA\Property(
     *          property="external",
     *          type="array",
     *
     *          @OA\Items(
     *              type="object",
     *
     *              @OA\Property(
     *                   property="name",
     *                   type="string",
     *              ),
     *              @OA\Property(
     *                   property="url",
     *                   type="string",
     *              ),
     *          ),
     *      ),
     *
     *  ),
     */
    public function toArray($request)
    {
        return [
            'mal_id' => $this->mal_id,
            'username' => $this->username,
            'url' => $this->url,
            'images' => $this->images,
            'last_online' => $this->last_online,
            'gender' => $this->gender,
            'birthday' => $this->birthday,
            'location' => $this->location,
            'joined' => $this->joined,
            'statistics' => [
                'anime'=> $this->anime_stats,
                'manga'=> $this->manga_stats,
            ],
            'favorites' => $this->favorites,
            'updates' => $this->last_updates,
            'about' => $this->about,
            'external' => $this->external_links
        ];
    }
}
