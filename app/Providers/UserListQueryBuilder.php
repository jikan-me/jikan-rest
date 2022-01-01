<?php

namespace App\Providers;

use Illuminate\Http\Request;
use Jikan\Request\User\UserAnimeListRequest;
use Jikan\Request\User\UserMangaListRequest;
use Jikan\Helper\Constants as JikanConstants;

class UserListQueryBuilder
{
    private const VALID_ANIME_ORDER_BY = [
        'title' => JikanConstants::USER_ANIME_LIST_ORDER_BY_TITLE,
        'finish_date' => JikanConstants::USER_ANIME_LIST_ORDER_BY_FINISHED_DATE,
        'start_date' => JikanConstants::USER_ANIME_LIST_ORDER_BY_STARTED_DATE,
        'finished_date' => JikanConstants::USER_ANIME_LIST_ORDER_BY_FINISHED_DATE, // Alias
        'started_date' => JikanConstants::USER_ANIME_LIST_ORDER_BY_STARTED_DATE, // Alias
        'score' => JikanConstants::USER_ANIME_LIST_ORDER_BY_SCORE,
        'last_updated' => JikanConstants::USER_ANIME_LIST_ORDER_BY_LAST_UPDATED,
        'type' => JikanConstants::USER_ANIME_LIST_ORDER_BY_TYPE,
        'rated' => JikanConstants::USER_ANIME_LIST_ORDER_BY_RATED,
        'rewatch' => JikanConstants::USER_ANIME_LIST_ORDER_BY_REWATCH_VALUE,
        'rewatch_value' => JikanConstants::USER_ANIME_LIST_ORDER_BY_REWATCH_VALUE, // Alias
        'priority' => JikanConstants::USER_ANIME_LIST_ORDER_BY_PRIORITY,
        'progress' => JikanConstants::USER_ANIME_LIST_ORDER_BY_PROGRESS,
        'episodes_watched' => JikanConstants::USER_ANIME_LIST_ORDER_BY_PROGRESS, // Alias
        'storage' => JikanConstants::USER_ANIME_LIST_ORDER_BY_STORAGE,
        'air_start' => JikanConstants::USER_ANIME_LIST_ORDER_BY_AIR_START,
        'air_end' => JikanConstants::USER_ANIME_LIST_ORDER_BY_AIR_END,
        'status' => JikanConstants::USER_ANIME_LIST_ORDER_BY_STATUS,
    ];

    private const VALID_MANGA_ORDER_BY = [
        'title' => JikanConstants::USER_MANGA_LIST_ORDER_BY_TITLE,
        'finish_date' => JikanConstants::USER_MANGA_LIST_ORDER_BY_FINISHED_DATE,
        'start_date' => JikanConstants::USER_MANGA_LIST_ORDER_BY_STARTED_DATE,
        'finished_date' => JikanConstants::USER_MANGA_LIST_ORDER_BY_FINISHED_DATE,
        'started_date' => JikanConstants::USER_MANGA_LIST_ORDER_BY_STARTED_DATE,
        'score' => JikanConstants::USER_MANGA_LIST_ORDER_BY_SCORE,
        'last_updated' => JikanConstants::USER_MANGA_LIST_ORDER_BY_LAST_UPDATED,
        'priority' => JikanConstants::USER_MANGA_LIST_ORDER_BY_PRIORITY,
        'progress' => JikanConstants::USER_MANGA_LIST_ORDER_BY_CHAPTERS,
        'chapters_read' => JikanConstants::USER_MANGA_LIST_ORDER_BY_CHAPTERS,
        'volumes_read' => JikanConstants::USER_MANGA_LIST_ORDER_BY_VOLUMES,
        'type' => JikanConstants::USER_MANGA_LIST_ORDER_BY_TYPE,
        'publish_start' => JikanConstants::USER_MANGA_LIST_ORDER_BY_PUBLISH_START,
        'publish_end' => JikanConstants::USER_MANGA_LIST_ORDER_BY_PUBLISH_END,
        'status' => JikanConstants::USER_MANGA_LIST_ORDER_BY_STATUS,
    ];

    private const VALID_SORT = [
        'ascending' => JikanConstants::USER_LIST_SORT_ASCENDING,
        'asc' => JikanConstants::USER_LIST_SORT_ASCENDING,
        'descending' => JikanConstants::USER_LIST_SORT_DESCENDING,
        'desc' => JikanConstants::USER_LIST_SORT_DESCENDING,
    ];

    private const VALID_SEASONS = [
        'winter' => JikanConstants::WINTER,
        'summer' => JikanConstants::SUMMER,
        'fall' => JikanConstants::FALL,
        'spring' => JikanConstants::SPRING,
    ];

    private const VALID_AIRING_STATUS = [
        'airing' => JikanConstants::USER_ANIME_LIST_CURRENTLY_AIRING,
        'finished' => JikanConstants::USER_ANIME_LIST_FINISHED_AIRING,
        'complete' => JikanConstants::USER_ANIME_LIST_FINISHED_AIRING,
        'to_be_aired' => JikanConstants::USER_ANIME_LIST_NOT_YET_AIRED,
        'not_yet_aired' => JikanConstants::USER_ANIME_LIST_NOT_YET_AIRED,
        'tba' => JikanConstants::USER_ANIME_LIST_NOT_YET_AIRED,
        'nya' => JikanConstants::USER_ANIME_LIST_NOT_YET_AIRED,
    ];

    private const VALID_PUBLISHING_STATUS = [
        'publishing' => JikanConstants::USER_MANGA_LIST_CURRENTLY_PUBLISHING,
        'finished' => JikanConstants::USER_MANGA_LIST_FINISHED_PUBLISHING,
        'complete' => JikanConstants::USER_MANGA_LIST_FINISHED_PUBLISHING,
        'to_be_published' => JikanConstants::USER_MANGA_LIST_NOT_YET_PUBLISHED,
        'not_yet_published' => JikanConstants::USER_MANGA_LIST_NOT_YET_PUBLISHED,
        'tba' => JikanConstants::USER_MANGA_LIST_NOT_YET_PUBLISHED,
        'nya' => JikanConstants::USER_MANGA_LIST_NOT_YET_PUBLISHED,
    ];

    public static function create(Request $request, $parser)
    {
        $query = $request->get('search') ?? null;
        $search = $request->get('q') ?? null;
        $page = $request->get('page') ?? null;
        $sort = $request->get('sort') ?? null;
        $orderBy = $request->get('order_by') ?? null;
        $orderBy2 = $request->get('order_by2') ?? null;

        // anime only
        $airedFrom = $request->get('aired_from') ?? null;
        $airedTo = $request->get('aired_to') ?? null;
        $producer = $request->get('producer') ?? null;
        $season = $request->get('season') ?? null;
        $year = $request->get('year') ?? null;
        $airingStatus = $request->get('airing_status') ?? null;

        // manga only
        $publishedFrom = $request->get('published_from') ?? null;
        $publishedTo = $request->get('published_to') ?? null;
        $magazine = $request->get('magazine') ?? null;
        $publishingStatus = $request->get('publishing_status') ?? null;


        // search
        if ($search !== null) {
            $parser->setTitle($search);
        }
        // bc: alias
        if ($query !== null) {
            $parser->setTitle($query);
        }

        // page
        if ($page !== null) {
            $parser->setPage((int) $page);
        }

        // sort
        if ($sort !== null && array_key_exists($sort, self::VALID_SORT)) {
            $sort = self::VALID_SORT[$sort];
        }

        // animelist only queries
        if ($parser instanceof UserAnimeListRequest) {

            // order by
            if ($orderBy !== null && array_key_exists($orderBy, self::VALID_ANIME_ORDER_BY)) {
                $orderBy = self::VALID_ANIME_ORDER_BY[$orderBy];

                $parser->setOrderBy($orderBy, $sort);
            }

            // order by 2
            if ($orderBy2 !== null && array_key_exists($orderBy2, self::VALID_ANIME_ORDER_BY)) {
                $orderBy2 = self::VALID_ANIME_ORDER_BY[$orderBy2];

                $parser->setOrderBy2($orderBy2, $sort);
            }

            // aired from
            if ($airedFrom !== null && preg_match("~[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}~", $airedFrom)) {
                $airedFrom = explode("-", $airedFrom);

                $parser->setAiredFrom(
                    (int) $airedFrom[0],
                    (int) $airedFrom[1],
                    (int) $airedFrom[2]
                );
            }

            // aired to
            if ($airedTo !== null) {
                if (preg_match("~[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}~", $airedTo)) {
                    $airedTo = explode("-", $airedTo);

                    $parser->setAiredTo(
                        (int) $airedTo[0],
                        (int) $airedTo[1],
                        (int) $airedTo[2]
                    );
                }
            }

            // producer
            if ($producer !== null) {
                $parser->setProducer((int) $producer);
            }

            // season
            if ($season !== null && in_array($season, self::VALID_SEASONS)) {
                $parser->setSeason($season);
            }

            // year
            if ($year !== null) {
                $parser->setSeasonYear($year);
            }

            // airing status
            if ($airingStatus !== null && array_key_exists($airingStatus, self::VALID_AIRING_STATUS)) {
                $airingStatus = self::VALID_AIRING_STATUS[$airingStatus];

                $parser->setAiringStatus($airingStatus);
            }

        }

        if ($parser instanceof UserMangaListRequest) {
            // order by
            if ($orderBy !== null && array_key_exists($orderBy, self::VALID_MANGA_ORDER_BY)) {
                $orderBy = self::VALID_MANGA_ORDER_BY[$orderBy];

                $parser->setOrderBy($orderBy, $sort);
            }

            // order by 2
            if ($orderBy2 !== null && array_key_exists($orderBy2, self::VALID_MANGA_ORDER_BY)) {
                $orderBy2 = self::VALID_MANGA_ORDER_BY[$orderBy2];

                $parser->setOrderBy2($orderBy2, $sort);
            }

            // published from
            if ($publishedFrom !== null) {
                if (preg_match("~[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}~", $publishedFrom)) {
                    $publishedFrom = explode("-", $publishedFrom);

                    $parser->setPublishedFrom(
                        (int) $publishedFrom[0],
                        (int) $publishedFrom[1],
                        (int) $publishedFrom[2]
                    );
                }
            }

            // published to
            if ($publishedTo !== null) {
                if (preg_match("~[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}~", $publishedTo)) {
                    $publishedTo = explode("-", $publishedTo);

                    $parser->setPublishedTo(
                        (int) $publishedTo[0],
                        (int) $publishedTo[1],
                        (int) $publishedTo[2]
                    );
                }
            }


            // magazine
            if ($magazine !== null) {
                $parser->setMagazine((int) $magazine);
            }

            // airing status
            if ($publishingStatus !== null && array_key_exists($publishingStatus, self::VALID_PUBLISHING_STATUS)) {
                $publishingStatus = self::VALID_PUBLISHING_STATUS[$publishingStatus];

                $parser->setPublishingStatus($publishingStatus);
            }

        }

        return $parser;
    }
}
