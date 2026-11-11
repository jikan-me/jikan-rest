<?php

namespace App\Http\Resources\V4;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'people_search', description: 'People Search', allOf: [new OA\Schema(ref: '#/components/schemas/pagination_plus'), new OA\Schema(properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(allOf: [new OA\Schema(ref: '#/components/schemas/person')]))])])]
class PersonCollection extends ResourceCollection
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = 'App\Http\Resources\V4\PersonResource';

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
