<?php

namespace App\Http\Resources\V4;

use Illuminate\Http\Resources\Json\JsonResource;

class AnimeThemesResource extends JsonResource
{

    /**
     *  @OA\Schema(
     *      schema="anime themes",
     *      description="Anime Opening and Ending Themes",
     *
     *      @OA\Property(
     *          property="data",
     *          type="object",
     *          @OA\Property(
     *               property="openings",
     *               type="array",
     *               @OA\Items(
     *                   type="string",
     *               ),
     *          ),
     *          @OA\Property(
     *               property="endings",
     *               type="array",
     *               @OA\Items(
     *                   type="string",
     *               ),
     *          ),
     *      ),
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
        return [
            'openings' => $this->opening_themes,
            'endings' => $this->ending_themes
        ];
    }
}