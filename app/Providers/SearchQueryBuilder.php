<?php

namespace App\Providers;

use \voku\helper\AntiXSS;
use Jikan\Helper\Constants as JikanConstants;

class SearchQueryBuilder
{

    private const VALID_SUB_TYPES = [
        'tv' => JikanConstants::SEARCH_ANIME_TV,
        'ova' => JikanConstants::SEARCH_ANIME_OVA,
        'movie' => JikanConstants::SEARCH_ANIME_MOVIE,
        'special' => JikanConstants::SEARCH_ANIME_SPECIAL,
        'ona' => JikanConstants::SEARCH_ANIME_ONA,
        'music' => JikanConstants::SEARCH_ANIME_MUSIC,
        'manga' => JikanConstants::SEARCH_MANGA_MANGA,
        'novel' => JikanConstants::SEARCH_MANGA_NOVEL,
        'oneshot' => JikanConstants::SEARCH_MANGA_ONESHOT,
        'doujin' => JikanConstants::SEARCH_MANGA_DOUJIN,
        'manhwa' => JikanConstants::SEARCH_MANGA_MANHWA,
        'manhua' => JikanConstants::SEARCH_MANGA_MANHUA
    ];

    private const VALID_STATUS = [
        'airing' => JikanConstants::SEARCH_ANIME_STATUS_AIRING,
        'completed' => JikanConstants::SEARCH_ANIME_STATUS_COMPLETED,
        'complete' => JikanConstants::SEARCH_ANIME_STATUS_COMPLETED,
        'tba' => JikanConstants::SEARCH_ANIME_STATUS_TBA,
        'upcoming' => JikanConstants::SEARCH_ANIME_STATUS_TBA
    ];
    private const VALID_RATING = [
        'g' => JikanConstants::SEARCH_ANIME_RATING_G,
        'pg' => JikanConstants::SEARCH_ANIME_RATING_PG,
        'pg13' => JikanConstants::SEARCH_ANIME_RATING_PG13,
        'r17' => JikanConstants::SEARCH_ANIME_RATING_R17,
        'r' => JikanConstants::SEARCH_ANIME_RATING_R,
        'rx' => JikanConstants::SEARCH_ANIME_RATING_RX
    ];

    private const VALID_GENRE = [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45];


    public static function create($request)
    {
        $xss = new AntiXSS();

        if (isset($_GET['q'])) {
            $request->setQuery(
                $xss->xss_clean($_GET['q'])
            );
        }

        if (isset($_GET['page'])) {
            $page = (int) $page;
            $request->setPage($page);
        }

        if (isset($_GET['type'])) {
            $subtype = strtolower($xss->xss_clean($_GET['type']));
            if (array_key_exists($subtype, self::VALID_SUB_TYPES)) {
                $request->setType(self::VALID_SUB_TYPES[$subtype]);
            }
        }

        if (isset($_GET['score'])) {
            $score = (float) $xss->xss_clean($_GET['score']);

            if ($score >= 0.0 && $score <= 10.0) {
                $request->setScore($score);
            }
        }

        if (isset($_GET['status'])) {
            $status = strtolower($xss->xss_clean($_GET['status']));
            if (array_key_exists($status, self::VALID_STATUS)) {
                $request->setStatus(self::VALID_STATUS[$status]);
            }
        }

        if (isset($_GET['rated'])) {
            $rated = strtolower($xss->xss_clean($_GET['rated']));
            if (array_key_exists($rated, self::VALID_RATING)) {
                $request->setRated(self::VALID_RATING[$rated]);
            }
        }

        if (isset($_GET['start_date'])) {
            $startDate = $xss->xss_clean($_GET['start_date']);
            if (preg_match("~[0-9]{4}-[0-9]{2}-[0-9]{2}~", $startDate)) {
                $startDate = explode("-", $startDate);
                $request->setStartDate(
                    (int) $startDate[2],
                    (int) $startDate[1],
                    (int) $startDate[0]
                );
            }
        }

        if (isset($_GET['end_date'])) {
            $endDate = $xss->xss_clean($_GET['end_date']);
            if (preg_match("~[0-9]{4}-[0-9]{2}-[0-9]{2}~", $endDate)) {
                $endDate = explode("-", $endDate);
                $request->setEndDate(
                    (int) $endDate[2],
                    (int) $endDate[1],
                    (int) $endDate[0]
                );
            }
        }

        if (isset($_GET['genre'])) {

            if (is_array($_GET['genre'])) {
                foreach ($_GET['genre'] as $genre) {
                    $genre = (int) $genre;

                    if (\in_array($genre, self::VALID_GENRE)) {
                        $request->setGenre($genre);
                    }
                }
            } else {
                $genre = (int) $_GET['genre'];
                if (\in_array($genre, self::VALID_GENRE)) {
                    $request->setGenre($genre);
                }
            }
        }

        if (isset($_GET['genre_exclude'])) {
            $request->setGenreExclude(
                ((int) $_GET['genre_exclude'] == 1) ? false : true
            );
        }

        return $request;
    }

}