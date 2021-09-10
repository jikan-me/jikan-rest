<?php


namespace App\Http\Resources\V4;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;

class InsightsCollection extends ResourceCollection
{

    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = 'App\Http\Resources\V4\InsightsResource';


    private $pagination;

    public function __construct(LengthAwarePaginator $resource)
    {
        $this->pagination = [
            'last_visible_page' => $resource->lastPage(),
            'has_next_page' => $resource->hasMorePages()
        ];

        $this->collection = $resource->getCollection();

        parent::__construct($resource);
    }

    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'pagination' => $this->pagination,
            'data' => $this->collection
        ];
    }

    public function withResponse($request, $response)
    {
        $jsonResponse = json_decode($response->getContent(), true);
        unset($jsonResponse['links'],$jsonResponse['meta']);
        $response->setContent(json_encode($jsonResponse));
    }
}