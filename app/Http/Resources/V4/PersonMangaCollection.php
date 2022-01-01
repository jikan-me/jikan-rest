<?php


namespace App\Http\Resources\V4;

use Illuminate\Http\Resources\Json\ResourceCollection;

class PersonMangaCollection extends ResourceCollection
{

    /**
     * The resource that this resource collects.
     *
     *  @OA\Schema(
     *      schema="person manga",
     *      description="Person's mangaography",
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
     *                  property="manga",
     *                  type="object",
     *                  ref="#/components/schemas/manga meta"
     *              ),
     *          ),
     *     ),
     *  )
     */
    public $collects = 'App\Http\Resources\V4\PersonMangaResource';

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