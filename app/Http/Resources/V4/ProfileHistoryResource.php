<?php

namespace App\Http\Resources\V4;

use Illuminate\Http\Resources\Json\JsonResource;

class ProfileHistoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     *
     * @OA\Schema(
     *     schema="user history",
     *     type="object",
     *
     *     @OA\Property(
     *          property="data",
     *          type="array",
     *          @OA\Items(
     *              type="object",
     *              @OA\Schema(ref="#/components/schemas/history"),
     *          ),
     *      ),
     *  ),
     * @OA\Schema(
     *     schema="history",
     *     type="object",
     *       @OA\Property(
     *           property="entry",
     *           type="object",
     *           description="Entry Meta",
     *           ref="#/components/schemas/mal_url"
     *       ),
     *      @OA\Property(
     *          property="increment",
     *          type="integer",
     *          description="Number of episodes/chapters watched/read"
     *      ),
     *      @OA\Property(
     *          property="date",
     *          type="string",
     *          description="Date ISO8601"
     *      ),
     * ),
     */
    public function toArray($request)
    {
        return $this['history'];
    }
}