<?php

namespace App\Http\Resources\V4;

use Illuminate\Http\Resources\Json\JsonResource;

class CommonResource extends JsonResource
{
    /**
     *  @OA\Schema(
     *      schema="trailer",
     *      type="object",
     *      description="Youtube Details",
     *
     *      @OA\Property(
     *          property="youtube_id",
     *          type="string",
     *          description="YouTube ID"
     *      ),
     *      @OA\Property(
     *          property="url",
     *          type="string",
     *          description="YouTube URL"
     *      ),
     *      @OA\Property(
     *          property="embed_url",
     *          type="string",
     *          description="Parsed Embed URL"
     *      ),
     *     @OA\Property(
     *          property="images",
     *          type="object",
     *          @OA\Property(
     *              property="default_image_url",
     *              type="string",
     *              description="Default Image Size URL (120x90)"
     *          ),
     *          @OA\Property(
     *              property="small_image_url",
     *              type="string",
     *              description="Small Image Size URL (640x480)"
     *          ),
     *          @OA\Property(
     *              property="medium_image_url",
     *              type="string",
     *              description="Medium Image Size URL (320x180)"
     *          ),
     *          @OA\Property(
     *              property="large_image_url",
     *              type="string",
     *              description="Large Image Size URL (480x360)"
     *          ),
     *          @OA\Property(
     *              property="maximum_image_url",
     *              type="string",
     *              description="Maximum Image Size URL (1280x720)"
     *          ),
     *     ),
     *  ),
     *
     *  @OA\Schema(
     *      schema="daterange",
     *      type="object",
     *      description="Date range",
     *
     *      @OA\Property(
     *          property="from",
     *          type="string",
     *          description="Date ISO8601"
     *      ),
     *      @OA\Property(
     *          property="to",
     *          type="string",
     *          description="Date ISO8601"
     *      ),
     *      @OA\Property(
     *          property="prop",
     *          type="object",
     *          description="Date Prop",
     *          @OA\Property(
     *              property="from",
     *              type="object",
     *              description="Date Prop From",
     *              @OA\Property(
     *                  property="day",
     *                  type="integer",
     *                  description="Day"
     *              ),
     *              @OA\Property(
     *                  property="month",
     *                  type="integer",
     *                  description="Month"
     *              ),
     *              @OA\Property(
     *                  property="year",
     *                  type="integer",
     *                  description="year"
     *              ),
     *          ),
     *          @OA\Property(
     *              property="to",
     *              type="object",
     *              description="Date Prop To",
     *              @OA\Property(
     *                  property="day",
     *                  type="integer",
     *                  description="Day"
     *              ),
     *              @OA\Property(
     *                  property="month",
     *                  type="integer",
     *                  description="Month"
     *              ),
     *              @OA\Property(
     *                  property="year",
     *                  type="integer",
     *                  description="year"
     *              ),
     *          ),
     *          @OA\Property(
     *              property="string",
     *              type="string",
     *              description="Raw parsed string"
     *          ),
     *      ),
     *  ),
     *
     *  @OA\Schema(
     *      schema="broadcast",
     *      type="object",
     *      description="Broadcast Details",
     *
     *      @OA\Property(
     *          property="day",
     *          type="string",
     *          description="Day of the week"
     *      ),
     *      @OA\Property(
     *          property="time",
     *          type="string",
     *          description="Time in 24 hour format"
     *      ),
     *      @OA\Property(
     *          property="timezone",
     *          type="string",
     *          description="Timezone (Tz Database format https://en.wikipedia.org/wiki/List_of_tz_database_time_zones)"
     *      ),
     *      @OA\Property(
     *          property="string",
     *          type="string",
     *          description="Raw parsed broadcast string"
     *      ),
     *  ),
     *
     *  @OA\Schema(
     *      schema="mal_url",
     *      type="object",
     *      description="Parsed URL Data",
     *      @OA\Property(
     *          property="mal_id",
     *          type="integer",
     *          description="MyAnimeList ID"
     *      ),
     *      @OA\Property(
     *          property="type",
     *          type="string",
     *          description="Type of resource"
     *      ),
     *      @OA\Property(
     *          property="name",
     *          type="string",
     *          description="Resource Name/Title"
     *      ),
     *      @OA\Property(
     *          property="url",
     *          type="string",
     *          description="MyAnimeList URL"
     *      ),
     *  ),
     *
     *  @OA\Schema(
     *      schema="entry_meta",
     *      type="object",
     *      description="Entry Meta data",
     *      @OA\Property(
     *          property="mal_id",
     *          type="integer",
     *          description="MyAnimeList ID"
     *      ),
     *      @OA\Property(
     *          property="url",
     *          type="string",
     *          description="MyAnimeList URL"
     *      ),
     *      @OA\Property(
     *          property="image_url",
     *          type="string",
     *          description="Image URL"
     *      ),
     *      @OA\Property(
     *          property="name",
     *          type="string",
     *          description="Entry Name/Title"
     *      ),
     *  ),
     *
     *  @OA\Schema(
     *      schema="relation",
     *      type="array",
     *      description="Related resources",
     *
     *     @OA\Items(
     *          type="object",
     *          @OA\Property(
     *              property="relation",
     *              type="string",
     *              description="Relation type"
     *          ),
     *          @OA\Property(
     *              property="items",
     *              type="array",
     *              description="Related items",
     *
     *              @OA\Items(
     *                  type="object",
     *                  ref="#/components/schemas/mal_url"
     *              ),
     *          ),
     *     ),
     *  ),
     *
     * @OA\Schema(
     *     schema="pagination",
     *     type="object",
     *     @OA\Property(
     *          property="last_visible_page",
     *          type="integer"
     *      ),
     *     @OA\Property(
     *          property="has_next_page",
     *          type="boolean"
     *     ),
     *  ),
     *
     * @OA\Schema(
     *     schema="news",
     *     type="object",
     *     @OA\Property(
     *          property="results",
     *          type="array",
     *          @OA\Items(
     *              type="object",
     *              @OA\Property(
     *                  property="mal_id",
     *                  type="integer",
     *                  description="MyAnimeList ID"
     *              ),
     *              @OA\Property(
     *                  property="url",
     *                  type="string",
     *                  description="MyAnimeList URL"
     *              ),
     *              @OA\Property(
     *                  property="title",
     *                  type="string",
     *                  description="Title"
     *              ),
     *              @OA\Property(
     *                  property="date",
     *                  type="string",
     *                  description="Post Date ISO8601"
     *              ),
     *              @OA\Property(
     *                  property="author_username",
     *                  type="string",
     *                  description="Author MyAnimeList Username"
     *              ),
     *              @OA\Property(
     *                  property="author_url",
     *                  type="string",
     *                  description="Author Profile URL"
     *              ),
     *              @OA\Property(
     *                  property="forum_url",
     *                  type="string",
     *                  description="Forum topic URL"
     *              ),
     *              @OA\Property(
     *                  property="image_url",
     *                  type="string",
     *                  description="Image URL"
     *              ),
     *              @OA\Property(
     *                  property="comments",
     *                  type="integer",
     *                  description="Comment count"
     *              ),
     *              @OA\Property(
     *                  property="excerpt",
     *                  type="string",
     *                  description="Excerpt"
     *              ),
     *         ),
     *     ),
     *  ),
     *
     * @OA\Schema(
     *     schema="anime review",
     *     type="object",
     *     @OA\Property(
     *         property="mal_id",
     *         type="integer",
     *         description="MyAnimeList ID"
     *     ),
     *     @OA\Property(
     *         property="url",
     *         type="string",
     *         description="MyAnimeList URL"
     *     ),
     *     @OA\Property(
     *         property="type",
     *         type="string",
     *         description="Entry Type"
     *     ),
     *     @OA\Property(
     *         property="votes",
     *         type="integer",
     *         description="Number of user votes on the Review"
     *     ),
     *     @OA\Property(
     *         property="date",
     *         type="string",
     *         description="Review created date ISO8601"
     *     ),
     *     @OA\Property(
     *         property="scores",
     *         type="object",
     *         description="Review Scores breakdown",
     *         @OA\Property(
     *             property="overall",
     *             type="integer",
     *             description="Overall Score"
     *         ),
     *         @OA\Property(
     *             property="story",
     *             type="integer",
     *             description="Story Score"
     *         ),
     *         @OA\Property(
     *             property="animation",
     *             type="integer",
     *             description="Animation Score"
     *         ),
     *         @OA\Property(
     *             property="sound",
     *             type="integer",
     *             description="Sound Score"
     *         ),
     *         @OA\Property(
     *             property="character",
     *             type="integer",
     *             description="Character Score"
     *         ),
     *         @OA\Property(
     *             property="enjoyment",
     *             type="integer",
     *             description="Enjoyment Score"
     *         ),
     *     ),
     *     @OA\Property(
     *         property="review",
     *         type="string",
     *         description="Review content"
     *     ),
     *     @OA\Property(
     *         property="reviewer",
     *         type="object",
     *         description="Reviewer details",
     *         @OA\Property(
     *             property="username",
     *             type="string",
     *             description="MyAnimeList Username"
     *         ),
     *         @OA\Property(
     *             property="url",
     *             type="string",
     *             description="MyAnimeList Profile URL"
     *         ),
     *         @OA\Property(
     *             property="image_url",
     *             type="string",
     *             description="User Display Picture Image URL"
     *         ),
     *         @OA\Property(
     *             property="episodes_seen",
     *             type="integer",
     *             description="Number of episodes seen"
     *         ),
     *     ),
     *  ),
     *
     * @OA\Schema(
     *     schema="manga review",
     *     type="object",
     *     @OA\Property(
     *         property="mal_id",
     *         type="integer",
     *         description="MyAnimeList ID"
     *     ),
     *     @OA\Property(
     *         property="url",
     *         type="string",
     *         description="MyAnimeList URL"
     *     ),
     *     @OA\Property(
     *         property="type",
     *         type="string",
     *         description="Entry Type"
     *     ),
     *     @OA\Property(
     *         property="votes",
     *         type="integer",
     *         description="Number of user votes on the Review"
     *     ),
     *     @OA\Property(
     *         property="date",
     *         type="string",
     *         description="Review created date ISO8601"
     *     ),
     *     @OA\Property(
     *         property="scores",
     *         type="object",
     *         description="Review Scores breakdown",
     *         @OA\Property(
     *             property="overall",
     *             type="integer",
     *             description="Overall Score"
     *         ),
     *         @OA\Property(
     *             property="story",
     *             type="integer",
     *             description="Story Score"
     *         ),
     *         @OA\Property(
     *             property="art",
     *             type="integer",
     *             description="Art Score"
     *         ),
     *         @OA\Property(
     *             property="character",
     *             type="integer",
     *             description="Character Score"
     *         ),
     *         @OA\Property(
     *             property="enjoyment",
     *             type="integer",
     *             description="Enjoyment Score"
     *         ),
     *     ),
     *     @OA\Property(
     *         property="review",
     *         type="string",
     *         description="Review content"
     *     ),
     *     @OA\Property(
     *         property="reviewer",
     *         type="object",
     *         description="Reviewer details",
     *         @OA\Property(
     *             property="username",
     *             type="string",
     *             description="MyAnimeList Username"
     *         ),
     *         @OA\Property(
     *             property="url",
     *             type="string",
     *             description="MyAnimeList Profile URL"
     *         ),
     *         @OA\Property(
     *             property="image_url",
     *             type="string",
     *             description="User Display Picture Image URL"
     *         ),
     *         @OA\Property(
     *             property="chapters_read",
     *             type="integer",
     *             description="Number of chapters read"
     *         ),
     *     ),
     *  ),
     */
}