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
     *  )
     */
}