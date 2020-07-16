<?php


namespace App\Http\Resources\V4;

use Illuminate\Http\Resources\Json\ResourceCollection;

class PersonCollection extends ResourceCollection
{

    /**
     * The resource that this resource collects.
     *
     * @var string
     *
     *  @OA\Schema(
     *      schema="people search",
     *      description="People Search Resource",
     *
     *     @OA\Property(
     *          property="data",
     *          type="object",
     *
     *          allOf={
     *              @OA\Schema(ref="#/components/schemas/pagination"),
     *              @OA\Schema(ref="#/components/schemas/person"),
     *          }
     *     ),
     *  )
     */
    public $collects = 'App\Http\Resources\V4\PersonResource';

    /**
     * Transform the resource collection into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'data' => $this->collection,
            'links' => [
                'self' => 'link-value',
            ],
        ];
    }
}