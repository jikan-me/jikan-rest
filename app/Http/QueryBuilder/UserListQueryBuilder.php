<?php

namespace App\Http\QueryBuilder;

use Jikan\Request\User\UserAnimeListRequest;
use Jikan\Request\User\UserMangaListRequest;
use Illuminate\Http\Request;
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

    public static function create(Request $request, $parserRequest)
    {
        $query = $request->get('q');
        $sort = $request->get('sort');
        $orderBy = $request->get('order_by');
        $orderBy2 = $request->get('order_by2');
        $airedFrom = $request->get('aired_from');
        $airedTo = $request->get('aired_to');
        $producer = $request->get('producer');
        $magazine = $request->get('magazine');
        $season = $request->get('season');
        $year = $request->get('year');
        $airingStatus = $request->get('airing_status');
        $publishedFrom = $request->get('published_from');
        $publishedTo = $request->get('published_to');
        $publishingStatus = $request->get('publishing_status');

        // Search
        if (!is_null($query)) {
            $parserRequest->setTitle($query);
        }

        // Page
        $parserRequest->setPage(
            (int) $request->get('page') ?? 1
        );

        // Sort
        $sort = $request->get('sort');
        if (!is_null($sort)) {

            if (array_key_exists($sort, self::VALID_SORT)) {
                $sort = self::VALID_SORT[$sort];
            }
        }

        if ($parserRequest instanceof UserAnimeListRequest) {
            // Order By
            if (!is_null($orderBy)) {

                if (array_key_exists($orderBy, self::VALID_ANIME_ORDER_BY)) {
                    $orderBy = self::VALID_ANIME_ORDER_BY[$orderBy];

                    $parserRequest->setOrderBy($orderBy, $sort);
                }
            }

            // Order By 2
            if (!is_null($orderBy2)) {

                if (array_key_exists($orderBy2, self::VALID_ANIME_ORDER_BY)) {
                    $orderBy2 = self::VALID_ANIME_ORDER_BY[$orderBy2];

                    $parserRequest->setOrderBy2($orderBy2, $sort);
                }
            }

            // Aired From
            if (!is_null($airedFrom)) {

                if (preg_match("~[0-9]{4}-[0-9]{2}-[0-9]{2}~", $airedFrom)) {
                    $airedFrom = explode("-", $airedFrom);
                    $parserRequest->setAiredFrom(
                        (int) $airedFrom[2],
                        (int) $airedFrom[1],
                        (int) $airedFrom[0]
                    );
                }
            }

            // Aired To
            if (!is_null($airedTo)) {

                if (preg_match("~[0-9]{4}-[0-9]{2}-[0-9]{2}~", $airedTo)) {
                    $airedTo = explode("-", $airedTo);
                    $parserRequest->setAiredTo(
                        (int) $airedTo[2],
                        (int) $airedTo[1],
                        (int) $airedTo[0]
                    );
                }
            }

            // Producer
            if (!is_null($producer)) {

                $parserRequest->setProducer(
                    (int) $producer
                );
            }

            // Season
            if (!is_null($season)) {

                if (\in_array($season, self::VALID_SEASONS)) {
                    $parserRequest->setSeason($season);
                }
            }

            // Year
            if (!is_null($year)) {

                $parserRequest->setSeasonYear(
                    (int) $year
                );
            }

            // Airing Status
            if (!is_null($airingStatus)) {

                if (array_key_exists($airingStatus, self::VALID_AIRING_STATUS)) {
                    $airingStatus = self::VALID_AIRING_STATUS[$airingStatus];
                    $parserRequest->setAiringStatus($airingStatus);
                }
            }
        }

        if ($parserRequest instanceof UserMangaListRequest) {
            // Order By
            if (!is_null($orderBy)) {

                if (array_key_exists($orderBy, self::VALID_MANGA_ORDER_BY)) {
                    $orderBy = self::VALID_MANGA_ORDER_BY[$orderBy];

                    $parserRequest->setOrderBy($orderBy, $sort);
                }
            }

            // Order By 2
            if (!is_null($orderBy2)) {

                if (array_key_exists($orderBy2, self::VALID_MANGA_ORDER_BY)) {
                    $orderBy2 = self::VALID_MANGA_ORDER_BY[$orderBy2];

                    $parserRequest->setOrderBy2($orderBy2, $sort);
                }
            }

            // Published From
            if (!is_null($publishedFrom)) {

                if (preg_match("~[0-9]{4}-[0-9]{2}-[0-9]{2}~", $publishedFrom)) {
                    $publishedFrom = explode("-", $publishedFrom);
                    $parserRequest->setPublishedFrom(
                        (int) $publishedFrom[2],
                        (int) $publishedFrom[1],
                        (int) $publishedFrom[0]
                    );
                }
            }

            // Published To
            if (!is_null($publishedTo)) {

                if (preg_match("~[0-9]{4}-[0-9]{2}-[0-9]{2}~", $publishedTo)) {
                    $publishedTo = explode("-", $publishedTo);
                    $parserRequest->setPublishedTo(
                        (int) $publishedTo[2],
                        (int) $publishedTo[1],
                        (int) $publishedTo[0]
                    );
                }
            }

            // Magazine
            if (!is_null($magazine)) {
                $parserRequest->setMagazine(
                    (int) $magazine
                );
            }

            // Publishing Status
            if (!is_null($publishingStatus)) {

                if (array_key_exists($publishingStatus, self::VALID_PUBLISHING_STATUS)) {
                    $publishingStatus = self::VALID_PUBLISHING_STATUS[$publishingStatus];
                    $parserRequest->setPublishingStatus($publishingStatus);
                }
            }
        }

        return $parserRequest;
    }
}
