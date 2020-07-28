<?php

namespace App\Http\Controllers\V3;

use App\Anime;
use App\Http\HttpHelper;
use App\Manga;
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
        $letter = $request->get('letter');
        $producer = $request->get('producer');

        $results = Anime::query();

        if (!empty($query)) {
            $results
                ->getQuery()->projections = ['distance_score'=>['$meta'=>'textScore']];

            $results
                ->orderBy('distance_score',['$meta'=>'textScore'])
                ->whereRaw([
                    '$text' => [
                        '$search' => "{$query}",
                        '$language' => 'en'
                    ],
                ]);
        }

        if (!is_null($letter)) {
            $results = $results
                ->where('title', 'like', "{$letter}%");
        }

        if (empty($query)) {
            $results = $results
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

        if (!is_null($producer)) {

            $producer = (int) $producer;

            $results = $results
                ->where('producers.mal_id', $producer);
        }

        if (!is_null($genres)) {
            $genres = explode(',', $genres);

            foreach ($genres as $genre) {
                if (empty($genre)) {
                    continue;
                }

                $genre = (int) $genre;

                $results = $results
                    ->where('genres.mal_id', $genre);
            }
        }

        if (!is_null($status)) {
            $results = $results
                ->where('status', $status);
        }

        if (!is_null($rating)) {
            $results = $results
                ->where('rating', $rating);
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
                ['mal_id', 'url', 'image_url', 'title', 'type', 'episodes', 'aired', 'airing', 'score', 'members', 'synopsis', 'rated'],
                null,
                $page
            );

        $items = $this->applyBackwardsCompatibility($results);

        return response()->json($items);
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
        $letter = $request->get('letter');
        $magazine = $request->get('magazine');

        $results = Manga::query();

        if (!empty($query)) {
            $results
                ->getQuery()->projections = ['distance_score'=>['$meta'=>'textScore']];

            $results
                ->orderBy('distance_score',['$meta'=>'textScore'])
                ->whereRaw([
                    '$text' => [
                        '$search' => "{$query}",
                        '$language' => 'en'
                    ],
                ]);
        }

        if (!is_null($letter)) {
            $results = $results
                ->where('title', 'like', "{$letter}%");
        }

        if (empty($query)) {
            $results = $results
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

        if (!is_null($magazine)) {

            $magazine = (int) $magazine;

            $results = $results
                ->where('serializations.mal_id', $magazine);
        }

        if (!is_null($genres)) {
            $genres = explode(',', $genres);

            foreach ($genres as $genre) {
                if (empty($genre)) {
                    continue;
                }

                $genre = (int) $genre;

                $results = $results
                    ->where('genres.mal_id', $genre);
            }
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
                ['mal_id', 'url', 'image_url', 'title', 'publishing', 'synopsis', 'type', 'chapters', 'volumes', 'score', 'published.from', 'published.to', 'members'],
                null,
                $page
            );

        $items = $this->applyBackwardsCompatibility($results);

        return response()->json($items);
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
            if (isset($item['aired'])) {
                $item['start_date'] = $item['aired']['from'] ?? null;
                $item['end_date'] = $item['aired']['to'] ?? null;
            }
            if (isset($item['published'])) {
                $item['start_date'] = $item['published']['from'] ?? null;
                $item['end_date'] = $item['published']['to'] ?? null;
            }

            if (isset($item['rating'])) {
                $item['rated'] = $item['rating'];
            }

            unset($item['_id'], $item['oid'], $item['expiresAt'], $item['aired'], $item['published'], $item['rating'], $item['distance_score']);
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
