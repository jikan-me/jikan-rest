<?php


namespace App\Http\Resources\V4;

use Illuminate\Http\Resources\Json\ResourceCollection;

class GenreCollection extends ResourceCollection
{

    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = 'App\Http\Resources\V4\GenreResource';

    /**
     * Transform the resource collection into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     *
     *  @OA\Schema(
     *      schema="genres",
     *      description="Genres Collection Resource",
     *
     *      @OA\Property(
     *          property="data",
     *          type="array",
     *          @OA\Items(
     *              type="object",
     *              ref="#/components/schemas/genre"
     *          ),
     *      ),
     *  ),
     *
     *  @OA\Schema(
     *    schema="genre query filter",
     *    description="Filter genres by type",
     *    type="string",
     *    enum={"genres","explicit_genres", "themes", "demographics"}
     *  )
     */
    public function toArray($request)
    {
        return $this->collection;
    }
}