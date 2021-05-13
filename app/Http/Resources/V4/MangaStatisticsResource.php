<?php

namespace App\Http\Resources\V4;

use Illuminate\Http\Resources\Json\JsonResource;

class MangaStatisticsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     *
     *  @OA\Schema(
     *      schema="manga statistics",
     *      description="Manga Statistics Resource",
     *
     *     @OA\Property(
     *          property="data",
     *          type="object",
     *
     *          @OA\Property(
     *              property="reading",
     *              type="integer",
     *              description="Number of users reading the resource"
     *          ),
     *          @OA\Property(
     *              property="completed",
     *              type="integer",
     *              description="Number of users who have completed the resource"
     *          ),
     *          @OA\Property(
     *              property="on_hold",
     *              type="integer",
     *              description="Number of users who have put the resource on hold"
     *          ),
     *          @OA\Property(
     *              property="dropped",
     *              type="integer",
     *              description="Number of users who have dropped the resource"
     *          ),
     *          @OA\Property(
     *              property="plan_to_read",
     *              type="integer",
     *              description="Number of users who have planned to read the resource"
     *          ),
     *          @OA\Property(
     *              property="total",
     *              type="integer",
     *              description="Total number of users who have the resource added to their lists"
     *          ),
     *
     *          @OA\Property(
     *               property="scores",
     *               type="array",
     *               @OA\Items(
     *                   type="object",
     *                   @OA\Property(
     *                       property="score",
     *                       type="integer",
     *                       description="Scoring value"
     *                   ),
     *                   @OA\Property(
     *                       property="votes",
     *                       type="integer",
     *                       description="Number of votes for this score"
     *                   ),
     *                   @OA\Property(
     *                       property="percentage",
     *                       type="number",
     *                       format="float",
     *                       description="Percentage of votes for this score"
     *                   ),
     *              ),
     *          ),
     *     ),
     *  )
     */
    public function toArray($request)
    {
        return [
            'reading' => $this['reading'],
            'completed' => $this['completed'],
            'on_hold' => $this['on_hold'],
            'dropped' => $this['dropped'],
            'plan_to_read' => $this['plan_to_read'],
            'total' => $this['total'],
            'scores' => $this['scores']
        ];
    }
}