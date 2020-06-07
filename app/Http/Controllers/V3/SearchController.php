<?php

namespace App\Http\Controllers\V3;

use App\Http\HttpHelper;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Jikan\Jikan;
use Jikan\MyAnimeList\MalClient;
use Jikan\Request\Search\AnimeSearchRequest;
use Jikan\Request\Search\MangaSearchRequest;
use Jikan\Request\Search\CharacterSearchRequest;
use Jikan\Request\Search\PersonSearchRequest;
use Jikan\Helper\Constants as JikanConstants;
use App\Providers\SearchQueryBuilder;
use JMS\Serializer\Serializer;
use MongoDB\BSON\UTCDateTime;
use phpDocumentor\Reflection\Types\Object_;

class SearchController extends Controller
{

    private $request;

    public function anime(Request $request, int $page = 1)
    {
        $this->request = $request;

        $query = $request->get('q');
        $limit = 50;
        $offset = $page*$limit;

        $results = DB::table('anime')
//            ->where([
//                ['title', 'like', "%${$query}%"],
//                ['title_english', 'like', "%${$query}%"],
//                ['title_japanese', 'like', "%${$query}%"],
//            ])
            ->where('title', 'like', "%$query%")
//            ->orWhere('title_english', 'like', "%$query%")
//            ->orWhere('title_japanese', 'like', "%$query%")
//            ->offset($offset)
//            ->limit($limit)
            ->select('mal_id', 'url', 'image_url', 'title', 'airing', 'synopsis', 'type', 'episodes', 'score', 'start_date', 'end_date', 'members', 'rated')
            ->paginate(50);

//        var_dump($results->items());
//        die;

        $items = $this->applyBackwardsCompatibility($results);

        return response()->json($items);


        $search = $this->jikan->getAnimeSearch(
            SearchQueryBuilder::create(
                (new AnimeSearchRequest())->setPage($page)
            )
        );

        return response($this->filter($search));
    }

    public function manga(int $page = 1)
    {
        $search = $this->jikan->getMangaSearch(
            SearchQueryBuilder::create(
                (new MangaSearchRequest())->setPage($page)
            )
        );
        return response($this->filter($search));
    }

    public function people(int $page = 1)
    {
        $search = $this->jikan->getPersonSearch(
            SearchQueryBuilder::create(
                (new PersonSearchRequest())->setPage($page)
            )
        );

        return response($this->filter($search));
    }

    public function character(int $page = 1)
    {
        $search = $this->jikan->getCharacterSearch(
            SearchQueryBuilder::create(
                (new CharacterSearchRequest())->setPage($page)
            )
        );

        return response($this->filter($search));
    }


    private function filter($object)
    {
        $limit = $_GET['limit'] ?? null;

        $data = json_decode(
            $this->serializer->serialize($object, 'json'),
            true
        );

        if (!is_null($limit)) {
            $data['results'] = array_slice($data['results'], 0, $limit);
        }

        return json_encode(
            $data
        );
    }

    private function applyBackwardsCompatibility($data)
    {
        $fingerprint = HttpHelper::resolveRequestFingerprint($this->request);

        $meta = [
            'request_hash' => $fingerprint,
            'request_cached' => true,
            'request_cache_expiry' => 0,
            'last_page' => $data->lastPage()
        ];

        $items = $data->items() ?? [];
        foreach ($items as &$item) {
            unset($item['_id'], $item['oid'], $item['expiresAt']);
        }
        $items = ['results' => $items];

        return $meta+$items;
    }

}
