<?php


namespace App\Http\Resources\V4;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;

class AnimeCollection extends ResourceCollection
{

    /**
     * The resource that this resource collects.
     *
     * @var string
     *
     *  @OA\Schema(
     *      schema="anime search",
     *      description="Anime Collection Resource",
     *
     *      allOf={
     *          @OA\Schema(ref="#/components/schemas/pagination"),
     *          @OA\Schema(
     *              @OA\Property(
     *                   property="data",
     *                   type="array",
     *
     *                   @OA\Items(
     *                          ref="#/components/schemas/anime"
     *                   )
     *              ),
     *          )
     *      }
     *  )
     */
    public $collects = 'App\Http\Resources\V4\AnimeResource';

    private $pagination;

    public function __construct($resource)
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
     * @param \Illuminate\Http\Request $request
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