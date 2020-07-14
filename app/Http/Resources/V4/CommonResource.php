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
     */
}