<?php


namespace App\Http\Resources\V4;

use Illuminate\Http\Resources\Json\ResourceCollection;

class MagazineCollection extends ResourceCollection
{

    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = 'App\Http\Resources\V4\MagazineResource';

    /**
     * Transform the resource collection into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     *
     *  @OA\Schema(
     *      schema="magazines",
     *      description="Magazine Collection Resource",
     *
     *     @OA\Property(
     *          property="data",
     *          type="object",
     *
     *          allOf={
     *              @OA\Schema(ref="#/components/schemas/pagination"),
     *              @OA\Schema(ref="#/components/schemas/producer"),
     *          }
     *     ),
     *  ),
     */
    public function toArray($request)
    {
        return [
            'data' => $this->collection
        ];
    }
}