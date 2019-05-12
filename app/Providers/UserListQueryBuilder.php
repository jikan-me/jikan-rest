<?php

namespace App\Providers;

use Jikan\Request\User\UserAnimeListRequest;
use Jikan\Request\User\UserMangaListRequest;
use \voku\helper\AntiXSS;
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

    public static function create($request)
    {
        $xss = new AntiXSS();

        // Search
        if (isset($_GET['search'])) {
            $request->setTitle(
                $xss->xss_clean($_GET['search'])
            );
        }

        // Search Alias
        if (isset($_GET['q'])) {
            $request->setTitle(
                $xss->xss_clean($_GET['q'])
            );
        }

        // Page
        if (isset($_GET['page'])) {
            $page = (int) $_GET['page'];
            $request->setPage($page);
        }

        // Sort
        $sort = null;
        if (isset($_GET['sort'])) {
            $sort = $xss->xss_clean($_GET['sort']);

            if (array_key_exists($sort, self::VALID_SORT)) {
                $sort = self::VALID_SORT[$sort];
            }
        }

        if ($request instanceof UserAnimeListRequest) {
            // Order By
            if (isset($_GET['order_by'])) {
                $orderby = $xss->xss_clean($_GET['order_by']);

                if (array_key_exists($orderby, self::VALID_ANIME_ORDER_BY)) {
                    $orderby = self::VALID_ANIME_ORDER_BY[$orderby];

                    $request->setOrderBy($orderby, $sort);
                }
            }

            // Order By 2
            if (isset($_GET['order_by2'])) {
                $orderby = $xss->xss_clean($_GET['order_by2']);

                if (array_key_exists($orderby, self::VALID_ANIME_ORDER_BY)) {
                    $orderby = self::VALID_ANIME_ORDER_BY[$orderby];

                    $request->setOrderBy2($orderby, $sort);
                }
            }

            // Aired From
            if (isset($_GET['aired_from'])) {
                $airedFrom = $xss->xss_clean($_GET['aired_from']);
                if (preg_match("~[0-9]{4}-[0-9]{2}-[0-9]{2}~", $airedFrom)) {
                    $airedFrom = explode("-", $airedFrom);
                    $request->setAiredFrom(
                        (int) $airedFrom[2],
                        (int) $airedFrom[1],
                        (int) $airedFrom[0]
                    );
                }
            }

            // Aired To
            if (isset($_GET['aired_to'])) {
                $airedTo = $xss->xss_clean($_GET['aired_to']);
                if (preg_match("~[0-9]{4}-[0-9]{2}-[0-9]{2}~", $airedTo)) {
                    $airedTo = explode("-", $airedTo);
                    $request->setAiredTo(
                        (int) $airedTo[2],
                        (int) $airedTo[1],
                        (int) $airedTo[0]
                    );
                }
            }

            // Producer
            if (isset($_GET['producer'])) {
                $producer = (int) $_GET['producer'];
                $request->setProducer($producer);
            }

            // Season
            if (isset($_GET['season'])) {
                $season = $xss->xss_clean($_GET['season']);
                if (\in_array($season, self::VALID_SEASONS)) {
                    $request->setSeason($season);
                }
            }

            // Year
            if (isset($_GET['year'])) {
                $year = (int) $_GET['year'];
                $request->setSeasonYear($year);
            }

            // Airing Status
            if (isset($_GET['airing_status'])) {
                $airingStatus = $xss->xss_clean($_GET['airing_status']);

                if (array_key_exists($airingStatus, self::VALID_AIRING_STATUS)) {
                    $airingStatus = self::VALID_AIRING_STATUS[$airingStatus];
                    $request->setAiringStatus($airingStatus);
                }
            }
        }

        if ($request instanceof UserMangaListRequest) {
            // Order By
            if (isset($_GET['order_by'])) {
                $orderby = $xss->xss_clean($_GET['order_by']);

                if (array_key_exists($orderby, self::VALID_MANGA_ORDER_BY)) {
                    $orderby = self::VALID_MANGA_ORDER_BY[$orderby];

                    $request->setOrderBy($orderby, $sort);
                }
            }

            // Order By 2
            if (isset($_GET['order_by2'])) {
                $orderby = $xss->xss_clean($_GET['order_by2']);

                if (array_key_exists($orderby, self::VALID_MANGA_ORDER_BY)) {
                    $orderby = self::VALID_MANGA_ORDER_BY[$orderby];

                    $request->setOrderBy2($orderby, $sort);
                }
            }

            // Published From
            if (isset($_GET['published_from'])) {
                $publishedFrom = $xss->xss_clean($_GET['published_from']);
                if (preg_match("~[0-9]{4}-[0-9]{2}-[0-9]{2}~", $publishedFrom)) {
                    $publishedFrom = explode("-", $publishedFrom);
                    $request->setPublishedFrom(
                        (int) $publishedFrom[2],
                        (int) $publishedFrom[1],
                        (int) $publishedFrom[0]
                    );
                }
            }

            // Published To
            if (isset($_GET['published_to'])) {
                $publishedTo = $xss->xss_clean($_GET['published_to']);
                if (preg_match("~[0-9]{4}-[0-9]{2}-[0-9]{2}~", $publishedTo)) {
                    $publishedTo = explode("-", $publishedTo);
                    $request->setPublishedTo(
                        (int) $publishedTo[2],
                        (int) $publishedTo[1],
                        (int) $publishedTo[0]
                    );
                }
            }

            // Magazine
            if (isset($_GET['magazine'])) {
                $magazine = (int) $_GET['magazine'];
                $request->setMagazine($magazine);
            }

            // Publishing Status
            if (isset($_GET['publishing_status'])) {
                $publishingStatus = $xss->xss_clean($_GET['publishing_status']);

                if (array_key_exists($publishingStatus, self::VALID_PUBLISHING_STATUS)) {
                    $publishingStatus = self::VALID_PUBLISHING_STATUS[$publishingStatus];
                    $request->setPublishingStatus($publishingStatus);
                }
            }
        }

        return $request;
    }

}