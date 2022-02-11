<?php


namespace App\Http\Resources\V4;

use Illuminate\Http\Resources\Json\ResourceCollection;

class CharacterSeiyuuCollection extends ResourceCollection
{

    /**
     * The resource that this resource collects.
     *
     *  @OA\Schema(
     *      schema="character_voice_actors",
     *      description="Character voice actors",
     *
     *     @OA\Property(
     *          property="data",
     *          type="array",
     *
     *          @OA\Items(
     *              type="object",
     *
     *              @OA\Property(
     *                  property="language",
     *                  type="string",
     *                  description="Character's Role"
     *              ),
     *              @OA\Property(
     *                  property="person",
     *                  type="object",
     *                  ref="#/components/schemas/person_meta"
     *              ),
     *          ),
     *     ),
     *  )
     */
    public $collects = 'App\Http\Resources\V4\CharacterSeiyuuResource';

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