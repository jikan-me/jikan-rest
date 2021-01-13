<?php


namespace App\Http\Resources\V4;

use Illuminate\Http\Resources\Json\ResourceCollection;

class MangaCollection extends ResourceCollection
{

    /**
     * The resource that this resource collects.
     *
     * @var string
     *
     *  @OA\Schema(
     *      schema="manga search",
     *      description="Manga Search Resource",
     * 
     *      allOf={
     *          @OA\Schema(ref="#/components/schemas/pagination"),
     *          @OA\Schema(
     *              @OA\Property(
     *                   property="data",
     *                   type="array",
     *
     *                   @OA\Items(
     *                       allOf={
     *                           @OA\Schema(ref="#/components/schemas/manga"),
     *                       }
     *                   )
     *              ),
     *          )
     *      }
     *  )
     */
    public $collects = 'App\Http\Resources\V4\MangaResource';

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