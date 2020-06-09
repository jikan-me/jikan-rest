<?php

namespace App\Http\Controllers\V3;

use App\Http\HttpHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jikan\Request\Top\TopAnimeRequest;
use Jikan\Request\Top\TopMangaRequest;
use Jikan\Request\Top\TopCharactersRequest;
use Jikan\Request\Top\TopPeopleRequest;
use Jikan\Helper\Constants as JikanConstants;

class TopController extends Controller
{

    private $request;
    const MAX_RESULTS_PER_PAGE = 50;

    public function anime(Request $request, int $page = 1, string $type = null)
    {
        $this->request = $request;

        $animeType = $this->getAnimeType($type);
        $filterType = $this->getFilterType($type);

        if (!is_null($type) && !\in_array(strtolower($type), [
                JikanConstants::TOP_AIRING,
                JikanConstants::TOP_UPCOMING,
                JikanConstants::TOP_TV,
                JikanConstants::TOP_MOVIE,
                JikanConstants::TOP_OVA,
                JikanConstants::TOP_SPECIAL,
                JikanConstants::TOP_BY_POPULARITY,
                JikanConstants::TOP_BY_FAVORITES,
            ])) {
            return response()->json([
                'error' => 'Bad Request'
            ])->setStatusCode(400);
        }
        $results = DB::table('anime')
            ->whereNotNull('rank')
            ->where('rank', '>', 0)
            ->orderBy('rank', 'asc')
            ->where('status', '!=', 'Not yet aired')
            ->where('rating', '!=', 'Rx - Hentai');

        if (!is_null($animeType)) {
            $results = $results
                ->where('type', $animeType);
        }

        if (!is_null($filterType) && $filterType === 'airing') {
            $results = $results
                ->where('airing', true);
        }

        if (!is_null($filterType) && $filterType === 'upcoming') {
            $results = $results
                ->where('status', 'Not yet aired');
        }

        if (!is_null($filterType) && $filterType === 'bypopularity') {
            $results = $results
                ->orderBy('members', 'desc');
        }

        if (!is_null($filterType) && $filterType === 'favorite') {
            $results = $results
                ->orderBy('favorites', 'desc');
        }
        $results = $results
            ->paginate(
                self::MAX_RESULTS_PER_PAGE,
                [
                    'mal_id', 'rank', 'title', 'url', 'image_url', 'type', 'episodes', 'aired.from', 'aired.to', 'members', 'score'
                ],
                null,
                $page
            );

        $items = $this->applyBackwardsCompatibility($results);

        return response()->json($items);
//
//        $anime = $this->jikan->getTopAnime(new TopAnimeRequest($page, $type));
//
//        $top = ['top' => $this->jikan->getTopAnime(new TopAnimeRequest($page, $type))];
//
//        return response($this->serializer->serialize($top, 'json'));
    }

    public function manga(Request $request, int $page = 1, string $type = null)
    {
        $this->request = $request;

        $mangaType = $this->getMangaType($type);
        $filterType = $this->getFilterType($type);

        if (!is_null($type) && !\in_array(
                strtolower($type),
                [
                    JikanConstants::TOP_MANGA,
                    JikanConstants::TOP_NOVEL,
                    JikanConstants::TOP_ONE_SHOT,
                    JikanConstants::TOP_DOUJINSHI,
                    JikanConstants::TOP_MANHWA,
                    JikanConstants::TOP_MANHUA,
                    JikanConstants::TOP_BY_POPULARITY,
                    JikanConstants::TOP_BY_FAVORITES,
                ]
            )) {
            return response()->json([
                'error' => 'Bad Request'
            ])->setStatusCode(400);
        }

        $results = DB::table('manga')
            ->whereNotNull('rank')
            ->where('rank', '>', 0)
            ->orderBy('rank', 'asc')
            ->where('type', '!=', 'Doujinshi');

        if (!is_null($mangaType)) {
            $results = $results
                ->where('type', $type);
        }

        if (!is_null($filterType) && $filterType === 'publishing') {
            $results = $results
                ->where('publishing', true);
        }

        if (!is_null($filterType) && $filterType === 'bypopularity') {
            $results = $results
                ->orderBy('popularity', 'desc');
        }

        if (!is_null($filterType) && $filterType === 'favorite') {
            $results = $results
                ->orderBy('favorites', 'desc');
        }

        $results = $results
            ->paginate(
                self::MAX_RESULTS_PER_PAGE,
                [
                    'mal_id', 'rank', 'title', 'url', 'image_url', 'type', 'episodes', 'aired.from', 'aired.to', 'members', 'score'
                ],
                null,
                $page
            );

        $items = $this->applyBackwardsCompatibility($results);

        return response()->json($items);

//        $top = ['top' => $this->jikan->getTopManga(new TopMangaRequest($page, $type))];
//
//        return response($this->serializer->serialize($top, 'json'));
    }

    public function people(int $page = 1)
    {
        $top = ['top' => $this->jikan->getTopPeople(new TopPeopleRequest($page))];

        return response($this->serializer->serialize($top, 'json'));
    }

    public function characters(int $page = 1)
    {
        $top = ['top' => $this->jikan->getTopCharacters(new TopCharactersRequest($page))];

        return response($this->serializer->serialize($top, 'json'));
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

            unset($item['_id'], $item['oid'], $item['expiresAt'], $item['aired'], $item['published']);
        }
        $items = ['top' => $items];

        return $meta+$items;
    }

    private $animeTypes = [
        'tv' => 'TV',
        'movie' => 'Movie',
        'ova' => 'OVA',
        'special' => 'Special',
        'ona' => 'ONA',
        'music' => 'Music',
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
        'novels' => 'Novel',
        'oneshots' => 'One-shot',
        'doujin' => 'Doujinshi',
        'manhwa' => 'Manhwa',
        'manhua' => 'Manhua'
    ];
    private function getMangaType($type)
    {
        if (is_null($type)) {
            return null;
        }

        $type = strtolower($type);

        return $this->mangaTypes[$type] ?? null;
    }

    private $filterTypes = [
        'airing', 'upcoming', 'bypopularity', 'favorites'
    ];
    private function getFilterType($type)
    {
        if (is_null($type)) {
            return null;
        }

        $type = strtolower($type);

        if (in_array($type, $this->filterTypes)) {
            return $type;
        }

        return null;
    }
}
