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
    const MAX_RESULTS_PER_PAGE = 50;

    public function anime(Request $request, int $page = 1)
    {
        $this->request = $request;

        $query = $request->get('q');
        $page = $request->get('page');
        $limit = $request->get('limit') ?? self::MAX_RESULTS_PER_PAGE;
        $score = $request->get('score') ?? 0;
        $type = $this->getAnimeType($request->get('type'));
        $status = $this->getStatusType($request->get('status'));
        $rating = $this->getRatingType($request->get('rated'));
        $genres = $request->get('genre');
        $orderBy = $this->getOrderBy($request->get('order_by'));
        $sort = $this->getSort($request->get('sort'));

        $results = DB::table('anime')
            ->select('mal_id', 'url', 'image_url', 'title', 'airing', 'synopsis', 'type', 'episodes', 'score', 'aired.from', 'aired.to', 'members', 'rating');

        if (!empty($query)) {
            $results
                ->where('title', 'like', "%$query%")
                ->orWhere('title_english', 'like', "%$query%")
                ->orWhere('title_japanese', 'like', "%$query%");
        } else {
            $results
                ->orderBy('mal_id');
        }

        if (!empty($type)) {
            $results = $results
                ->where('type', $type);
        }

        if (!empty($score)) {
            $score = (float) $score;
            $results = $results
                ->where('score', '>=', $score);
        }

        if (!is_null($status)) {
            $results = $results
                ->where('status', $status);
        }

        if (!is_null($rating)) {
            $results = $results
                ->where('rating', $rating);
        }

        if (!is_null($genres)) {
            $genres = explode(',', $genres);

            // @todo WIP. Need genre indexing
        }

        if (!is_null($orderBy)) {
            $results = $results
                ->orderBy($orderBy, $sort ?? 'asc');
        }

        if (!empty($limit)) {
            $limit = (int) $limit;

            if ($limit <= 0) {
                $limit = 1;
            }

            if ($limit > self::MAX_RESULTS_PER_PAGE) {
                $limit = self::MAX_RESULTS_PER_PAGE;
            }
        }

        $results = $results
            ->paginate(
                $limit,
                null,
                null,
                $page
            );

        $items = $this->applyBackwardsCompatibility($results);

        return response()->json($items);


//        $search = $this->jikan->getAnimeSearch(
//            SearchQueryBuilder::create(
//                (new AnimeSearchRequest())->setPage($page)
//            )
//        );
//
//        return response($this->filter($search));
    }

    public function manga(Request $request, int $page = 1)
    {
        $this->request = $request;

        $query = $request->get('q');
        $page = $request->get('page');
        $limit = $request->get('limit') ?? self::MAX_RESULTS_PER_PAGE;
        $score = $request->get('score') ?? 0;
        $type = $this->getAnimeType($request->get('type'));
        $status = $this->getStatusType($request->get('status'));
        $genres = $request->get('genre');
        $orderBy = $this->getOrderBy($request->get('order_by'));
        $sort = $this->getSort($request->get('sort'));

        $results = DB::table('manga')
            ->select('mal_id', 'url', 'image_url', 'title', 'publishing', 'synopsis', 'type', 'chapters', 'volumes', 'score', 'published.from', 'published.to', 'members');

        if (!empty($query)) {
            $results
                ->where('title', 'like', "%$query%")
                ->orWhere('title_english', 'like', "%$query%")
                ->orWhere('title_japanese', 'like', "%$query%");
        } else {
            $results
                ->orderBy('mal_id');
        }

        if (!empty($type)) {
            $results = $results
                ->where('type', $type);
        }

        if (!empty($score)) {
            $score = (float) $score;
            $results = $results
                ->where('score', '>=', $score);
        }

        if (!is_null($status)) {
            $results = $results
                ->where('status', $status);
        }


        if (!is_null($genres)) {
            $genres = explode(',', $genres);

            // @todo WIP. Need genre indexing
        }

        if (!is_null($orderBy)) {
            $results = $results
                ->orderBy($orderBy, $sort ?? 'asc');
        }

        if (!empty($limit)) {
            $limit = (int) $limit;

            if ($limit <= 0) {
                $limit = 1;
            }

            if ($limit > self::MAX_RESULTS_PER_PAGE) {
                $limit = self::MAX_RESULTS_PER_PAGE;
            }
        }

        $results = $results
            ->paginate(
                $limit,
                null,
                null,
                $page
            );

        $items = $this->applyBackwardsCompatibility($results);

        return response()->json($items);

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
            if (isset($item['aired']['from'])) {
                $item['start_date'] = $item['aired']['from'];
            }

            if (isset($item['aired']['to'])) {
                $item['end_date'] = $item['aired']['to'];
            }
            if (isset($item['published']['from'])) {
                $item['start_date'] = $item['published']['from'];
            }

            if (isset($item['published']['to'])) {
                $item['end_date'] = $item['published']['to'];
            }

            if (isset($item['rating'])) {
                $item['rated'] = $item['rating'];
            }

            unset($item['_id'], $item['oid'], $item['expiresAt'], $item['aired'], $item['published'], $item['rating']);
        }
        $items = ['results' => $items];

        return $meta+$items;
    }

    private $animeTypes = [
        'tv' => 'TV',
        'movie' => 'Movie',
        'ova' => 'OVA',
        'special' => 'Special',
        'ona' => 'ONA',
        'music' => 'Music'
    ];
    private function getAnimeType($type)
    {
        if (is_null($type)) {
            return null;
        }

        $type = strtolower($type);

        return $this->animeTypes[$type] ?? null;
    }

    private $mangaTypes = [
        'manga' => 'Manga',
        'novel' => 'Novel',
        'oneshot' => 'One-shot',
        'doujin' => 'Doujinshi',
        'manhwa' => 'Manhwa',
        'manhua' => 'Manhua'
    ];
    private function getMangaTypes($type)
    {
        if (is_null($type)) {
            return null;
        }

        $type = strtolower($type);

        return $this->mangaTypes[$type] ?? null;
    }

    private $statusTypes = [
        'airing' => 'Currently Airing',
        'completed' => 'Finished Airing',
        'complete' => 'Finished Airing',
        'to_be_aired' => 'Not yet aired',
        'tba' => 'Not yet aired',
        'upcoming' => 'Not yet aired',
    ];
    private function getStatusType($type)
    {
        if (is_null($type)) {
            return null;
        }

        $type = strtolower($type);

        return $this->statusTypes[$type] ?? null;
    }

    private $ratingType = [
        'g' => 'G - All Ages',
        'pg' => 'PG - Children',
        'pg13' => 'PG-13 - Teens 13 or older',
        'r17' => 'R - 17+ (violence & profanity)',
        'r' => 'R+ - Mild Nudity',
        'rx' => 'Rx - Hentai'
    ];
    private function getRatingType($type)
    {
        if (is_null($type)) {
            return null;
        }

        $type = strtolower($type);

        return $this->ratingType[$type] ?? null;
    }

    private $orderByType = [
        'title' => 'title',
        'start_date' => 'aired.from',
        'end_date' => 'aired.to',
        'score' => 'score',
        'type' => 'type',
        'members' => 'members',
        'id' => 'mal_id',
        'episodes' => 'episodes',
        'chapters' => 'chapters',
        'volumes' => 'volumes',
        'rating' => 'rating'
    ];
    private function getOrderBy($orderBy) {
        $orderBy = strtolower($orderBy);

        if (!in_array($orderBy, [
            'title', 'start_date', 'end_date', 'score', 'type', 'members', 'id', 'episodes', 'rating'
        ])) {
            return null;
        }


        return $this->orderByType[$orderBy] ?? null;
    }

    private $sortType = [
        'ascending' => 'asc',
        'asc' => 'asc',
        'descending' => 'desc',
        'desc' => 'desc',
    ];
    private function getSort($sort) {
        if (is_null($sort)) {
            return null;
        }

        $sort = strtolower($sort);

        return $this->sortType[$sort] ?? null;
    }
}
