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
     *      allOf={
     *          @OA\Schema(ref="#/components/schemas/trailer_base"),
     *          @OA\Schema(ref="#/components/schemas/trailer_images"),
     *      }
     *  ),
     *
     *  @OA\Schema(
     *      schema="trailer_base",
     *      type="object",
     *      description="Youtube Details",
     *
     *      @OA\Property(
     *          property="youtube_id",
     *          type="string",
     *          description="YouTube ID",
     *          nullable=true
     *      ),
     *      @OA\Property(
     *          property="url",
     *          type="string",
     *          description="YouTube URL",
     *          nullable=true
     *      ),
     *      @OA\Property(
     *          property="embed_url",
     *          type="string",
     *          description="Parsed Embed URL",
     *          nullable=true
     *      ),
     *  ),
     *
     *  @OA\Schema(
     *      schema="trailer_images",
     *      type="object",
     *      description="Youtube Images",
     *
     *      @OA\Property(
     *          property="images",
     *          type="object",
     *          @OA\Property(
     *              property="image_url",
     *              type="string",
     *              description="Default Image Size URL (120x90)",
     *              nullable=true
     *          ),
     *          @OA\Property(
     *              property="small_image_url",
     *              type="string",
     *              description="Small Image Size URL (640x480)",
     *              nullable=true
     *          ),
     *          @OA\Property(
     *              property="medium_image_url",
     *              type="string",
     *              description="Medium Image Size URL (320x180)",
     *              nullable=true
     *          ),
     *          @OA\Property(
     *              property="large_image_url",
     *              type="string",
     *              description="Large Image Size URL (480x360)",
     *              nullable=true
     *          ),
     *          @OA\Property(
     *              property="maximum_image_url",
     *              type="string",
     *              description="Maximum Image Size URL (1280x720)",
     *              nullable=true
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
     *          description="Date ISO8601",
     *          nullable=true
     *      ),
     *      @OA\Property(
     *          property="to",
     *          type="string",
     *          description="Date ISO8601",
     *          nullable=true
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
     *                  description="Day",
     *                  nullable=true
     *              ),
     *              @OA\Property(
     *                  property="month",
     *                  type="integer",
     *                  description="Month",
     *                  nullable=true
     *              ),
     *              @OA\Property(
     *                  property="year",
     *                  type="integer",
     *                  description="Year",
     *                  nullable=true
     *              ),
     *          ),
     *          @OA\Property(
     *              property="to",
     *              type="object",
     *              description="Date Prop To",
     *              @OA\Property(
     *                  property="day",
     *                  type="integer",
     *                  description="Day",
     *                  nullable=true
     *              ),
     *              @OA\Property(
     *                  property="month",
     *                  type="integer",
     *                  description="Month",
     *                  nullable=true
     *              ),
     *              @OA\Property(
     *                  property="year",
     *                  type="integer",
     *                  description="Year",
     *                  nullable=true
     *              ),
     *          ),
     *          @OA\Property(
     *              property="string",
     *              type="string",
     *              description="Raw parsed string",
     *              nullable=true
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
     *          description="Day of the week",
     *          nullable=true
     *      ),
     *      @OA\Property(
     *          property="time",
     *          type="string",
     *          description="Time in 24 hour format",
     *          nullable=true
     *      ),
     *      @OA\Property(
     *          property="timezone",
     *          type="string",
     *          description="Timezone (Tz Database format https://en.wikipedia.org/wiki/List_of_tz_database_time_zones)",
     *          nullable=true
     *      ),
     *      @OA\Property(
     *          property="string",
     *          type="string",
     *          description="Raw parsed broadcast string",
     *          nullable=true
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
     *      schema="mal_url_2",
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
     *          property="title",
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
     *      type="object",
     *      description="Related resources",
     *
     *      @OA\Property(
     *          property="relation",
     *          type="string",
     *          description="Relation type"
     *      ),
     *      @OA\Property(
     *          property="entry",
     *          type="array",
     *          description="Related entries",
     *
     *          @OA\Items(
     *              type="object",
     *              ref="#/components/schemas/mal_url"
     *          ),
     *      ),
     *  ),
     *
     * @OA\Schema(
     *     schema="pagination",
     *     type="object",
     *
     *     @OA\Property(
     *          property="pagination",
     *          type="object",
     *
     *          @OA\Property(
     *               property="last_visible_page",
     *               type="integer"
     *           ),
     *          @OA\Property(
     *               property="has_next_page",
     *               type="boolean"
     *          ),
     *      ),
     *  ),
     *
     * @OA\Schema(
     *     schema="pagination_plus",
     *     type="object",
     *
     *     @OA\Property(
     *          property="pagination",
     *          type="object",
     *
     *          @OA\Property(
     *               property="last_visible_page",
     *               type="integer"
     *           ),
     *          @OA\Property(
     *               property="has_next_page",
     *               type="boolean"
     *          ),
     *
     *          @OA\Property (
     *              property="items",
     *              type="object",
     *
     *              @OA\Property (
     *                  property="count",
     *                  type="integer"
     *              ),
     *              @OA\Property (
     *                  property="total",
     *                  type="integer"
     *              ),
     *              @OA\Property (
     *                  property="per_page",
     *                  type="integer"
     *              ),
     *          )
     *      ),
     *  ),
     *
     *
     * @OA\Schema(
     *     schema="user_meta",
     *     type="object",
     *     @OA\Property(
     *         property="username",
     *         type="string",
     *         description="MyAnimeList Username"
     *     ),
     *     @OA\Property(
     *         property="url",
     *         type="string",
     *         description="MyAnimeList Profile URL"
     *     ),
     *     @OA\Property(
     *         property="images",
     *         type="object",
     *         ref="#/components/schemas/user_images",
     *     ),
     * ),
     *
     *  @OA\Schema(
     *      schema="user_by_id",
     *      description="User Meta By ID",
     *
     *      @OA\Property(
     *          property="url",
     *          type="string",
     *          description="MyAnimeList URL"
     *      ),
     *      @OA\Property(
     *          property="username",
     *          type="string",
     *          description="MyAnimeList Username"
     *      ),
     *  ),
     *
     * @OA\Schema(
     *     schema="user_images",
     *     type="object",
     *     @OA\Property(
     *         property="jpg",
     *         type="object",
     *         description="Available images in JPG",
     *         @OA\Property(
     *             property="image_url",
     *             type="string",
     *             description="Image URL JPG",
     *             nullable=true
     *         ),
     *     ),
     *     @OA\Property(
     *         property="webp",
     *         type="object",
     *         description="Available images in WEBP",
     *         @OA\Property(
     *             property="image_url",
     *             type="string",
     *             description="Image URL WEBP",
     *             nullable=true
     *         ),
     *     ),
     * ),
     *
     * @OA\Schema(
     *     schema="anime_meta",
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
     *         property="images",
     *         type="object",
     *         ref="#/components/schemas/anime_images",
     *     ),
     *     @OA\Property(
     *         property="title",
     *         type="string",
     *         description="Entry title"
     *     ),
     * ),
     *
     * @OA\Schema(
     *     schema="manga_meta",
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
     *         property="images",
     *         type="object",
     *         ref="#/components/schemas/manga_images",
     *     ),
     *     @OA\Property(
     *         property="title",
     *         type="string",
     *         description="Entry title"
     *     ),
     * ),
     *
     * @OA\Schema(
     *     schema="character_meta",
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
     *         property="images",
     *         type="object",
     *         ref="#/components/schemas/character_images",
     *     ),
     *     @OA\Property(
     *         property="name",
     *         type="string",
     *         description="Entry name"
     *     ),
     * ),
     *
     * @OA\Schema(
     *     schema="person_meta",
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
     *         property="images",
     *         type="object",
     *         ref="#/components/schemas/people_images",
     *     ),
     *     @OA\Property(
     *         property="name",
     *         type="string",
     *         description="Entry name"
     *     ),
     * ),
     *
     * @OA\Schema(
     *     schema="anime_images",
     *     type="object",
     *     @OA\Property(
     *         property="jpg",
     *         type="object",
     *         description="Available images in JPG",
     *         @OA\Property(
     *             property="image_url",
     *             type="string",
     *             description="Image URL JPG",
     *             nullable=true
     *         ),
     *         @OA\Property(
     *             property="small_image_url",
     *             type="string",
     *             description="Small Image URL JPG",
     *             nullable=true
     *         ),
     *         @OA\Property(
     *             property="large_image_url",
     *             type="string",
     *             description="Image URL JPG",
     *             nullable=true
     *         ),
     *     ),
     *     @OA\Property(
     *         property="webp",
     *         type="object",
     *         description="Available images in WEBP",
     *         @OA\Property(
     *             property="image_url",
     *             type="string",
     *             description="Image URL WEBP",
     *             nullable=true
     *         ),
     *         @OA\Property(
     *             property="small_image_url",
     *             type="string",
     *             description="Small Image URL WEBP",
     *             nullable=true
     *         ),
     *         @OA\Property(
     *             property="large_image_url",
     *             type="string",
     *             description="Image URL WEBP",
     *             nullable=true
     *         ),
     *     ),
     * ),
     *
     * @OA\Schema(
     *     schema="manga_images",
     *     type="object",
     *     @OA\Property(
     *         property="jpg",
     *         type="object",
     *         description="Available images in JPG",
     *         @OA\Property(
     *             property="image_url",
     *             type="string",
     *             description="Image URL JPG",
     *             nullable=true
     *         ),
     *         @OA\Property(
     *             property="small_image_url",
     *             type="string",
     *             description="Small Image URL JPG",
     *             nullable=true
     *         ),
     *         @OA\Property(
     *             property="large_image_url",
     *             type="string",
     *             description="Image URL JPG",
     *             nullable=true
     *         ),
     *     ),
     *     @OA\Property(
     *         property="webp",
     *         type="object",
     *         description="Available images in WEBP",
     *         @OA\Property(
     *             property="image_url",
     *             type="string",
     *             description="Image URL WEBP",
     *             nullable=true
     *         ),
     *         @OA\Property(
     *             property="small_image_url",
     *             type="string",
     *             description="Small Image URL WEBP",
     *             nullable=true
     *         ),
     *         @OA\Property(
     *             property="large_image_url",
     *             type="string",
     *             description="Image URL WEBP",
     *             nullable=true
     *         ),
     *     ),
     * ),
     *
     * @OA\Schema(
     *     schema="character_images",
     *     type="object",
     *     @OA\Property(
     *         property="jpg",
     *         type="object",
     *         description="Available images in JPG",
     *         @OA\Property(
     *             property="image_url",
     *             type="string",
     *             description="Image URL JPG",
     *             nullable=true
     *         ),
     *         @OA\Property(
     *             property="small_image_url",
     *             type="string",
     *             description="Small Image URL JPG",
     *             nullable=true
     *         ),
     *     ),
     *     @OA\Property(
     *         property="webp",
     *         type="object",
     *         description="Available images in WEBP",
     *         @OA\Property(
     *             property="image_url",
     *             type="string",
     *             description="Image URL WEBP",
     *             nullable=true
     *         ),
     *         @OA\Property(
     *             property="small_image_url",
     *             type="string",
     *             description="Small Image URL WEBP",
     *             nullable=true
     *         ),
     *     ),
     * ),
     *
     * @OA\Schema(
     *     schema="people_images",
     *     type="object",
     *     @OA\Property(
     *         property="jpg",
     *         type="object",
     *         description="Available images in JPG",
     *         @OA\Property(
     *             property="image_url",
     *             type="string",
     *             description="Image URL JPG",
     *             nullable=true
     *         ),
     *     ),
     * ),
     *
     * @OA\Schema(
     *     schema="common_images",
     *     type="object",
     *     @OA\Property(
     *         property="jpg",
     *         type="object",
     *         description="Available images in JPG",
     *         @OA\Property(
     *             property="image_url",
     *             type="string",
     *             description="Image URL JPG",
     *             nullable=true
     *         ),
     *     ),
     * ),
     *
     * @OA\Schema(
     *     schema="title",
     *     type="object",
     *     @OA\Property(
     *         property="type",
     *         type="string",
     *         description="Title type",
     *     ),
     *     @OA\Property(
     *         property="title",
     *         type="string",
     *         description="Title value",
     *     ),
     * ),
     *
     *
     */
}
