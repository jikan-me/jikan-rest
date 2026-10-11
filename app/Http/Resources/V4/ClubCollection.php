<?php

namespace App\Http\Resources\V4;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
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
     * @param  Request  $request
     * @return array
     *
     *  @OA\Schema(
     *      schema="clubs_search",
     *      description="Clubs Search Resource",
     *
     *      allOf={
     *          @OA\Schema(ref="#/components/schemas/pagination"),
     *          @OA\Schema(
     *
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
    private $pagination;

    /**
     * @param  LengthAwarePaginator<int, mixed>  $resource
     */
    public function __construct(LengthAwarePaginator $resource)
    {
        /** @var \Illuminate\Pagination\LengthAwarePaginator<int, mixed> $resource */
        $this->pagination = [
            'last_visible_page' => $resource->lastPage(),
            'has_next_page' => $resource->hasMorePages(),
        ];

        $this->collection = $resource->getCollection();

        parent::__construct($resource);
    }

    /**
     * Transform the resource collection into an array.
     *
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'pagination' => $this->pagination,
            'data' => $this->collection,
        ];
    }

    public function withResponse($request, $response)
    {
        $jsonResponse = json_decode($response->getContent(), true);
        unset($jsonResponse['links'],$jsonResponse['meta']);
        $response->setContent(json_encode($jsonResponse));
    }
}
