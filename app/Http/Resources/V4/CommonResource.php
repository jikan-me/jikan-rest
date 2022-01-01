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
     *          @OA\Schema(ref="#/components/schemas/trailer base"),
     *          @OA\Schema(ref="#/components/schemas/trailer images"),
     *      } 
     *  ),
     * 
     *  @OA\Schema(
     *      schema="trailer base",
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
     *  ),
     * 
     *  @OA\Schema(
     *      schema="trailer images",
     *      type="object",
     *      description="Youtube Images",
     *
     *      @OA\Property(
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
     *
     * @OA\Schema(
     *     schema="user meta",
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
     *         ref="#/components/schemas/user images",
     *     ),
     * ),
     *
     *  @OA\Schema(
     *      schema="user by id",
     *      description="User Meta By ID",
     *
     *      @OA\Property(
     *           property="data",
     *           type="object",
     *
     *           @OA\Property(
     *               property="url",
     *               type="string",
     *               description="MyAnimeList URL"
     *           ),
     *           @OA\Property(
     *               property="username",
     *               type="string",
     *               description="MyAnimeList Username"
     *           ),
     *      ),
     *  ),
     *
     * @OA\Schema(
     *     schema="user images",
     *     type="object",
     *     @OA\Property(
     *         property="jpg",
     *         type="object",
     *         description="Available images in JPG",
     *         @OA\Property(
     *             property="image_url",
     *             type="string",
     *             description="Image URL JPG (225x335)",
     *         ),
     *     ),
     *     @OA\Property(
     *         property="webp",
     *         type="object",
     *         description="Available images in WEBP",
     *         @OA\Property(
     *             property="image_url",
     *             type="string",
     *             description="Image URL WEBP (225x335)",
     *         ),
     *     ),
     * ),
     *
     * @OA\Schema(
     *     schema="anime meta",
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
     *         ref="#/components/schemas/anime images",
     *     ),
     *     @OA\Property(
     *         property="title",
     *         type="string",
     *         description="Entry title"
     *     ),
     * ),
     *
     * @OA\Schema(
     *     schema="manga meta",
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
     *         ref="#/components/schemas/manga images",
     *     ),
     *     @OA\Property(
     *         property="title",
     *         type="string",
     *         description="Entry title"
     *     ),
     * ),
     *
     * @OA\Schema(
     *     schema="character meta",
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
     *         ref="#/components/schemas/character images",
     *     ),
     *     @OA\Property(
     *         property="name",
     *         type="string",
     *         description="Entry name"
     *     ),
     * ),
     *
     * @OA\Schema(
     *     schema="person meta",
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
     *         ref="#/components/schemas/people images",
     *     ),
     *     @OA\Property(
     *         property="name",
     *         type="string",
     *         description="Entry name"
     *     ),
     * ),
     *
     * @OA\Schema(
     *     schema="anime images",
     *     type="object",
     *     @OA\Property(
     *         property="jpg",
     *         type="object",
     *         description="Available images in JPG",
     *         @OA\Property(
     *             property="image_url",
     *             type="string",
     *             description="Image URL JPG (225x335)",
     *         ),
     *         @OA\Property(
     *             property="small_image_url",
     *             type="string",
     *             description="Small Image URL JPG (50x74)",
     *         ),
     *         @OA\Property(
     *             property="large_image_url",
     *             type="string",
     *             description="Image URL JPG (300x446)",
     *         ),
     *     ),
     *     @OA\Property(
     *         property="webp",
     *         type="object",
     *         description="Available images in WEBP",
     *         @OA\Property(
     *             property="image_url",
     *             type="string",
     *             description="Image URL WEBP (225x335)",
     *         ),
     *         @OA\Property(
     *             property="small_image_url",
     *             type="string",
     *             description="Small Image URL WEBP (50x74)",
     *         ),
     *         @OA\Property(
     *             property="large_image_url",
     *             type="string",
     *             description="Image URL WEBP (300x446)",
     *         ),
     *     ),
     * ),
     *
     * @OA\Schema(
     *     schema="manga images",
     *     type="object",
     *     @OA\Property(
     *         property="jpg",
     *         type="object",
     *         description="Available images in JPG",
     *         @OA\Property(
     *             property="image_url",
     *             type="string",
     *             description="Image URL JPG (225x335)",
     *         ),
     *         @OA\Property(
     *             property="small_image_url",
     *             type="string",
     *             description="Small Image URL JPG (50x74)",
     *         ),
     *         @OA\Property(
     *             property="large_image_url",
     *             type="string",
     *             description="Image URL JPG (300x446)",
     *         ),
     *     ),
     *     @OA\Property(
     *         property="webp",
     *         type="object",
     *         description="Available images in WEBP",
     *         @OA\Property(
     *             property="image_url",
     *             type="string",
     *             description="Image URL WEBP (225x335)",
     *         ),
     *         @OA\Property(
     *             property="small_image_url",
     *             type="string",
     *             description="Small Image URL WEBP (50x74)",
     *         ),
     *         @OA\Property(
     *             property="large_image_url",
     *             type="string",
     *             description="Image URL WEBP (300x446)",
     *         ),
     *     ),
     * ),
     *
     * @OA\Schema(
     *     schema="character images",
     *     type="object",
     *     @OA\Property(
     *         property="jpg",
     *         type="object",
     *         description="Available images in JPG",
     *         @OA\Property(
     *             property="image_url",
     *             type="string",
     *             description="Image URL JPG (225x335)",
     *         ),
     *         @OA\Property(
     *             property="small_image_url",
     *             type="string",
     *             description="Small Image URL JPG (50x74)",
     *         ),
     *     ),
     *     @OA\Property(
     *         property="webp",
     *         type="object",
     *         description="Available images in WEBP",
     *         @OA\Property(
     *             property="image_url",
     *             type="string",
     *             description="Image URL WEBP (225x335)",
     *         ),
     *         @OA\Property(
     *             property="small_image_url",
     *             type="string",
     *             description="Small Image URL WEBP (50x74)",
     *         ),
     *     ),
     * ),
     *
     * @OA\Schema(
     *     schema="people images",
     *     type="object",
     *     @OA\Property(
     *         property="jpg",
     *         type="object",
     *         description="Available images in JPG",
     *         @OA\Property(
     *             property="image_url",
     *             type="string",
     *             description="Image URL JPG (225x335)",
     *         ),
     *     ),
     * ),
     *
     * @OA\Schema(
     *     schema="common images",
     *     type="object",
     *     @OA\Property(
     *         property="jpg",
     *         type="object",
     *         description="Available images in JPG",
     *         @OA\Property(
     *             property="image_url",
     *             type="string",
     *             description="Image URL JPG (225x335)",
     *         ),
     *     ),
     * ),
     *
     *
     */
}