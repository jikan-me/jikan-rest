<?php

namespace App\Http\Resources\V4;

use Illuminate\Http\Resources\Json\JsonResource;

class ClubStaffResource extends JsonResource
{

    /**
     *  @OA\Schema(
     *      schema="club staff",
     *      description="Club Staff Resource",
     *
     *     @OA\Property(
     *          property="data",
     *          type="array",
     *
     *          @OA\Items(
     *               type="object",
     *
     *              @OA\Property(
     *                  property="url",
     *                  type="string",
     *                  description="User URL",
     *              ),
     *              @OA\Property(
     *                  property="username",
     *                  type="string",
     *                  description="User's username",
     *              ),
     *          ),
     *     ),
     *  )
     */

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return $this['staff'];
    }
}