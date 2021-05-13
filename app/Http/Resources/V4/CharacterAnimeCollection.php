<?php


namespace App\Http\Resources\V4;

use Illuminate\Http\Resources\Json\ResourceCollection;

class CharacterAnimeCollection extends ResourceCollection
{

    /**
     * The resource that this resource collects.
     *
     *  @OA\Schema(
     *      schema="character anime",
     *      description="Character casted in anime",
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
     *                  description="Character's Role"
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
    public $collects = 'App\Http\Resources\V4\CharacterAnimeResource';

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