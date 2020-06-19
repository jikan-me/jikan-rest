<?php

namespace App\Http\Controllers\V4DB;

use App\Anime;
use App\Http\Resources\V4\AnimeCollection;
use Illuminate\Http\Request;
use Jikan\Request\Producer\ProducerRequest;
use Jikan\Request\Producer\ProducersRequest;

class ProducerController extends Controller
{

    private $request;
    const MAX_RESULTS_PER_PAGE = 50;

    public function main()
    {
        $results = $this->jikan->getProducers(new ProducersRequest());
        return response($this->serializer->serialize($results, 'json'));
    }

    public function resource(Request $request, int $id)
    {
        $this->request = $request;
        $page = $this->request->get('page') ?? 1;

        $results = Anime::query()
            ->where('producers.mal_id', $id)
            ->orderBy('title');

        $results = $results
            ->paginate(
                self::MAX_RESULTS_PER_PAGE,
                ['*'],
                null,
                $page
            );

        return new AnimeCollection(
            $results
        );
    }
}
