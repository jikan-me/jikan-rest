<?php

namespace App\Http\Resources\V4;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
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
     * @param  Request  $request
     * @return array
     *
     *  @OA\Schema(
     *      schema="magazines",
     *      description="Magazine Collection Resource",
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
     *                          ref="#/components/schemas/magazine"
     *                   )
     *              ),
     *          )
     *      }
     *
     *  ),
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
            'current_page' => $resource->currentPage(),
            'items' => [
                'count' => $resource->count(),
                'total' => $resource->total(),
                'per_page' => $resource->perPage(),
            ],
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
