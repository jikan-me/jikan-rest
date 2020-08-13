<?php


namespace App\Http\Resources\V4;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ClubCollection extends ResourceCollection
{

    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = 'App\Http\Resources\V4\ClubResource';

    /**
     * Transform the resource collection into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     *
     *  @OA\Schema(
     *      schema="clubs search",
     *      description="Clubs Search Resource",
     *
     *     @OA\Property(
     *          property="data",
     *          type="object",
     *
     *          allOf={
     *              @OA\Schema(ref="#/components/schemas/pagination"),
     *              @OA\Schema(ref="#/components/schemas/club"),
     *          }
     *     ),
     *      allOf={
     *          @OA\Schema(ref="#/components/schemas/pagination"),
     *          @OA\Schema(
     *              @OA\Property(
     *                   property="data",
     *                   type="array",
     *
     *                   @OA\Items(
     *                          ref="#/components/schemas/club"
     *                   )
     *              ),
     *          )
     *      }
     *  )
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