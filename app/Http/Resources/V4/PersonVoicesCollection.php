<?php


namespace App\Http\Resources\V4;

use Illuminate\Http\Resources\Json\ResourceCollection;

class PersonVoicesCollection extends ResourceCollection
{

    /**
     * The resource that this resource collects.
     *
     *  @OA\Schema(
     *      schema="person voice acting roles",
     *      description="Person's voice acting roles",
     *
     *     @OA\Property(
     *          property="data",
     *          type="array",
     *
     *          @OA\Items(
     *              type="object",
     *
     *              @OA\Property(
     *                  property="role",
     *                  type="string",
     *                  description="Person's Character's role in the anime"
     *              ),
     *              @OA\Property(
     *                  property="anime",
     *                  type="object",
     *                  description="The anime in which the person is voice acting in",
     *                  ref="#/components/schemas/anime meta"
     *              ),
     *              @OA\Property(
     *                  property="character",
     *                  type="object",
     *                  description="The character the person is voice acting for",
     *                  ref="#/components/schemas/character meta"
     *              ),
     *          ),
     *     ),
     *  )
     */
    public $collects = 'App\Http\Resources\V4\PersonVoiceResource';

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