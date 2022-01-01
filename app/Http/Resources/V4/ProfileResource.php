<?php

namespace App\Http\Resources\V4;

use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     *
     *
     * @OA\Schema(
     *     schema="user profile",
     *     type="object",
     *
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
     *           property="images",
     *           type="object",
     *           ref="#/components/schemas/user images"
     *       ),
     *      @OA\Property(
     *          property="last_online",
     *          type="string",
     *          description="Last Online Date ISO8601"
     *      ),
     *      @OA\Property(
     *          property="gender",
     *          type="string",
     *          description="User Gender"
     *      ),
     *      @OA\Property(
     *          property="birthday",
     *          type="string",
     *          description="Birthday Date ISO8601"
     *      ),
     *      @OA\Property(
     *          property="location",
     *          type="string",
     *          description="Location"
     *      ),
     *      @OA\Property(
     *          property="joined",
     *          type="string",
     *          description="Joined Date ISO8601"
     *      ),
     *  ),
     * @OA\Schema(
     *     schema="users temp",
     *     type="object",
     *
     *     @OA\Property(
     *          property="data",
     *          type="array",
     *          @OA\Items(
     *              type="object",
     *
     *              @OA\Property(
     *                  property="mal_id",
     *                  type="integer",
     *                  description="MyAnimeList ID"
     *              ),
     *              @OA\Property(
     *                  property="username",
     *                  type="string",
     *                  description="MyAnimeList Username"
     *              ),
     *              @OA\Property(
     *                  property="url",
     *                  type="string",
     *                  description="MyAnimeList URL"
     *              ),
     *               @OA\Property(
     *                   property="images",
     *                   type="object",
     *                   description="Images",
     *                   @OA\Property(
     *                       property="jpg",
     *                       type="object",
     *                       description="Available images in JPG",
     *                       @OA\Property(
     *                           property="image_url",
     *                           type="string",
     *                           description="Image URL JPG (225x335)",
     *                       ),
     *                   ),
     *                   @OA\Property(
     *                       property="webp",
     *                       type="object",
     *                       description="Available images in WEBP",
     *                       @OA\Property(
     *                           property="image_url",
     *                           type="string",
     *                           description="Image URL WEBP (225x335)",
     *                       ),
     *                   ),
     *               ),
     *              @OA\Property(
     *                  property="last_online",
     *                  type="string",
     *                  description="Last Online Date ISO8601"
     *              ),
     *              @OA\Property(
     *                  property="gender",
     *                  type="string",
     *                  description="User Gender"
     *              ),
     *              @OA\Property(
     *                  property="birthday",
     *                  type="string",
     *                  description="Birthday Date ISO8601"
     *              ),
     *              @OA\Property(
     *                  property="location",
     *                  type="string",
     *                  description="Location"
     *              ),
     *              @OA\Property(
     *                  property="joined",
     *                  type="string",
     *                  description="Joined Date ISO8601"
     *              ),
     *              @OA\Property(
     *                  property="anime_stats",
     *                  type="object",
     *                  description="Anime Stats",
     *                  @OA\Property(
     *                      property="days_watched",
     *                      type="number",
     *                      format="float",
     *                      description="Number of days spent watching Anime"
     *                  ),
     *                  @OA\Property(
     *                      property="mean_score",
     *                      type="number",
     *                      format="float",
     *                      description="Mean Score"
     *                  ),
     *                  @OA\Property(
     *                      property="watching",
     *                      type="integer",
     *                      description="Anime Watching"
     *                  ),
     *                  @OA\Property(
     *                      property="completed",
     *                      type="integer",
     *                      description="Anime Completed"
     *                  ),
     *                  @OA\Property(
     *                      property="on_hold",
     *                      type="integer",
     *                      description="Anime On-Hold"
     *                  ),
     *                  @OA\Property(
     *                      property="dropped",
     *                      type="integer",
     *                      description="Anime Dropped"
     *                  ),
     *                  @OA\Property(
     *                      property="plan_to_watch",
     *                      type="integer",
     *                      description="Anime Planned to Watch"
     *                  ),
     *                  @OA\Property(
     *                      property="total_entries",
     *                      type="integer",
     *                      description="Total Anime entries on User list"
     *                  ),
     *                  @OA\Property(
     *                      property="rewatched",
     *                      type="integer",
     *                      description="Anime re-watched"
     *                  ),
     *                  @OA\Property(
     *                      property="episodes_watched",
     *                      type="integer",
     *                      description="Number of Anime Episodes Watched"
     *                  ),
     *              ),
     *              @OA\Property(
     *                  property="manga_stats",
     *                  type="object",
     *                  description="Manga Stats",
     *                  @OA\Property(
     *                      property="days_read",
     *                      type="number",
     *                      format="float",
     *                      description="Number of days spent reading Manga"
     *                  ),
     *                  @OA\Property(
     *                      property="mean_score",
     *                      type="number",
     *                      format="float",
     *                      description="Mean Score"
     *                  ),
     *                  @OA\Property(
     *                      property="reading",
     *                      type="integer",
     *                      description="Manga Reading"
     *                  ),
     *                  @OA\Property(
     *                      property="completed",
     *                      type="integer",
     *                      description="Manga Completed"
     *                  ),
     *                  @OA\Property(
     *                      property="on_hold",
     *                      type="integer",
     *                      description="Manga On-Hold"
     *                  ),
     *                  @OA\Property(
     *                      property="dropped",
     *                      type="integer",
     *                      description="Manga Dropped"
     *                  ),
     *                  @OA\Property(
     *                      property="plan_to_read",
     *                      type="integer",
     *                      description="Manga Planned to Read"
     *                  ),
     *                  @OA\Property(
     *                      property="total_entries",
     *                      type="integer",
     *                      description="Total Manga entries on User list"
     *                  ),
     *                  @OA\Property(
     *                      property="reread",
     *                      type="integer",
     *                      description="Manga re-read"
     *                  ),
     *                  @OA\Property(
     *                      property="chapters_read",
     *                      type="integer",
     *                      description="Number of Manga Chapters Read"
     *                  ),
     *                  @OA\Property(
     *                      property="volumes_read",
     *                      type="integer",
     *                      description="Number of Manga Volumes Read"
     *                  ),
     *              ),
     *              @OA\Property(
     *                  property="favorites",
     *                  type="object",
     *                  description="Favorite entries",
     *                  @OA\Property(
     *                      property="anime",
     *                      type="array",
     *                      description="Favorite Anime",
     *                      @OA\Items(
     *                           type="object",
     *                           ref="#/components/schemas/entry_meta",
     *                      ),
     *                  ),
     *                  @OA\Property(
     *                      property="manga",
     *                      type="array",
     *                      description="Favorite Manga",
     *                      @OA\Items(
     *                           type="object",
     *                           ref="#/components/schemas/entry_meta",
     *                      ),
     *                  ),
     *                  @OA\Property(
     *                      property="characters",
     *                      type="array",
     *                      description="Favorite Characters",
     *                      @OA\Items(
     *                           type="object",
     *                           ref="#/components/schemas/entry_meta",
     *                      ),
     *                  ),
     *                  @OA\Property(
     *                      property="people",
     *                      type="array",
     *                      description="Favorite People",
     *                      @OA\Items(
     *                           type="object",
     *                           ref="#/components/schemas/entry_meta",
     *                      ),
     *                  ),
     *              ),
     *              @OA\Property(
     *                  property="about",
     *                  type="string",
     *                  description="User About. NOTE: About information is customizable by users through BBCode on MyAnimeList. This means users can add multimedia content, different text sizes, etc. Due to this freeform, Jikan returns parsed HTML. Validate on your end!"
     *              ),
     *          ),
     *      ),
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
        ];
    }
}