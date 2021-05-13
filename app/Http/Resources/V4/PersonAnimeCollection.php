<?php


namespace App\Http\Resources\V4;

use Illuminate\Http\Resources\Json\ResourceCollection;

class PersonAnimeCollection extends ResourceCollection
{

    /**
     * The resource that this resource collects.
     *
     *  @OA\Schema(
     *      schema="person anime",
     *      description="Person anime staff positions",
     *
     *     @OA\Property(
     *          property="data",
     *          type="array",
     *
     *          @OA\Items(
     *              type="object",
     *
     *              @OA\Property(
     *                  property="position",
     *                  type="string",
     *                  description="Person's position"
     *              ),
     *              @OA\Property(
     *                  property="anime",
     *                  type="object",
     *                  ref="#/components/schemas/anime meta"
     *              ),
     *          ),
     *     ),
     *  )
     */
    public $collects = 'App\Http\Resources\V4\PersonAnimeResource';

    /**
     * Transform the resource collection into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'data' => $this->collection
        ];
    }
}