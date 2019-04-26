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

    private const VALID_MIN_GENRE = 1;
    private const VALID_MAX_GENRE = 45;

    public static function create($request)
    {
        $xss = new AntiXSS();

        if (isset($_GET['q'])) {
            $request->setQuery(
                $xss->xss_clean($_GET['q'])
            );
        }

        if (isset($_GET['page'])) {
            $page = (int) $_GET['page'];
            $request->setPage($page);
        }

        if (isset($_GET['letter'])) {
            $letter = $xss->xss_clean($_GET['letter']);

            $request->setStartsWithChar('');

            if (!empty($_GET['letter'])) {
                $letter = strtolower(
                    // https://stackoverflow.com/questions/1972100/getting-the-first-character-of-a-string-with-str0#comment27161857_1972111
                    mb_substr($letter, 0, 1, 'utf-8')
                );

                if (preg_match('~[A-Z0-9\.]~', $letter)) {
                    $request->setStartsWithChar($letter);
                }
            }
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
            if (\is_array($_GET['genre'])) {
                foreach ($_GET['genre'] as $genre) {
                    $genre = (int) $genre;

                    if ($genre >= self::VALID_MIN_GENRE && $genre <= self::VALID_MAX_GENRE) {
                        $request->setGenre($genre);
                    }
                }
            }

            if (!\is_array($_GET['genre'])) {
                $genre = (int) $_GET['genre'];

                if ($genre >= self::VALID_MIN_GENRE && $genre <= self::VALID_MAX_GENRE) {
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