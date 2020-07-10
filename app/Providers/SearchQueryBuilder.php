<?php

namespace App\Providers;

use Jikan\Helper\Constants;
use Jikan\Model\Anime\Anime;
use Jikan\Request\Search\AnimeSearchRequest;
use Jikan\Request\Search\CharacterSearchRequest;
use Jikan\Request\Search\MangaSearchRequest;
use Jikan\Request\Search\PersonSearchRequest;
use Jikan\Request\Search\UserSearchRequest;
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

    private const VALID_ANIME_ORDER_BY = [
        'title' => JikanConstants::SEARCH_ANIME_ORDER_BY_TITLE,
        'start_date' => JikanConstants::SEARCH_ANIME_ORDER_BY_START_DATE,
        'start-date' => JikanConstants::SEARCH_ANIME_ORDER_BY_START_DATE,
        'startdate' => JikanConstants::SEARCH_ANIME_ORDER_BY_START_DATE,
        'score' => JikanConstants::SEARCH_ANIME_ORDER_BY_SCORE,
        'episodes' => JikanConstants::SEARCH_ANIME_ORDER_BY_EPISODES,
        'eps' => JikanConstants::SEARCH_ANIME_ORDER_BY_EPISODES,
        'end_date' => JikanConstants::SEARCH_ANIME_ORDER_BY_END_DATE,
        'end-date' => JikanConstants::SEARCH_ANIME_ORDER_BY_END_DATE,
        'enddate' => JikanConstants::SEARCH_ANIME_ORDER_BY_END_DATE,
        'type' => JikanConstants::SEARCH_ANIME_ORDER_BY_TYPE,
        'members' => JikanConstants::SEARCH_ANIME_ORDER_BY_MEMBERS,
        'rating' => JikanConstants::SEARCH_ANIME_ORDER_BY_RATED,
        'rated' => JikanConstants::SEARCH_ANIME_ORDER_BY_RATED,
        'id' => JikanConstants::SEARCH_ANIME_ORDER_BY_ID
    ];

    private const VALID_MANGA_ORDER_BY = [
        'title' => JikanConstants::SEARCH_MANGA_ORDER_BY_TITLE,
        'start_date' => JikanConstants::SEARCH_MANGA_ORDER_BY_START_DATE,
        'start-date' => JikanConstants::SEARCH_MANGA_ORDER_BY_START_DATE,
        'startdate' => JikanConstants::SEARCH_MANGA_ORDER_BY_START_DATE,
        'score' => JikanConstants::SEARCH_MANGA_ORDER_BY_SCORE,
        'volumes' => JikanConstants::SEARCH_MANGA_ORDER_BY_VOLUMES,
        'vols' => JikanConstants::SEARCH_MANGA_ORDER_BY_VOLUMES,
        'end_date' => JikanConstants::SEARCH_MANGA_ORDER_BY_END_DATE,
        'end-date' => JikanConstants::SEARCH_MANGA_ORDER_BY_END_DATE,
        'enddate' => JikanConstants::SEARCH_MANGA_ORDER_BY_END_DATE,
        'chapters' => JikanConstants::SEARCH_MANGA_ORDER_BY_CHAPTERS,
        'chaps' => JikanConstants::SEARCH_MANGA_ORDER_BY_CHAPTERS,
        'members' => JikanConstants::SEARCH_MANGA_ORDER_BY_MEMBERS,
        'type' => JikanConstants::SEARCH_MANGA_ORDER_BY_TYPE,
        'id' => JikanConstants::SEARCH_MANGA_ORDER_BY_ID,
    ];

    private const VALID_SORT = [
        'ascending' => JikanConstants::SEARCH_SORT_ASCENDING,
        'asc' => JikanConstants::SEARCH_SORT_ASCENDING,
        'descending' => JikanConstants::SEARCH_SORT_DESCENDING,
        'desc' => JikanConstants::SEARCH_SORT_DESCENDING,
    ];

    private const VALID_GENDER = [
        'any' => JikanConstants::SEARCH_USER_GENDER_ANY,
        'male' => JikanConstants::SEARCH_USER_GENDER_MALE,
        'female' => JikanConstants::SEARCH_USER_GENDER_FEMALE,
        'nonbinary' => JikanConstants::SEARCH_USER_GENDER_NONBINARY
    ];

    public static function create($request)
    {
        $xss = new AntiXSS();

        // Query
        if (isset($_GET['q'])) {
            $request->setQuery(
                $xss->xss_clean($_GET['q'])
            );
        }

        // Page
        if (isset($_GET['page'])) {
            $page = (int) $_GET['page'];
            $request->setPage($page);
        }

        if (
            $request instanceof AnimeSearchRequest
            || $request instanceof MangaSearchRequest
            || $request instanceof PersonSearchRequest
            || $request instanceof CharacterSearchRequest
        ) {

            // Starts with glyph
            if (isset($_GET['letter'])) {
                $letter = $xss->xss_clean($_GET['letter']);

                $request->setStartsWithChar('');

                if (!empty($_GET['letter'])) {
                    $letter =
                        // https://stackoverflow.com/questions/1972100/getting-the-first-character-of-a-string-with-str0#comment27161857_1972111
                        mb_substr($letter, 0, 1, 'utf-8');

                    $request->setStartsWithChar($letter);
                }
            }
        }


        // Anime & Manga
        if ($request instanceof AnimeSearchRequest || $request instanceof MangaSearchRequest) {
            // Type
            if (isset($_GET['type'])) {
                $subtype = strtolower($xss->xss_clean($_GET['type']));
                if (array_key_exists($subtype, self::VALID_SUB_TYPES)) {
                    $request->setType(self::VALID_SUB_TYPES[$subtype]);
                }
            }

            // Score
            if (isset($_GET['score'])) {
                $score = (float) $xss->xss_clean($_GET['score']);

                if ($score >= 0.0 && $score <= 10.0) {
                    $request->setScore($score);
                }
            }

            // Status
            if (isset($_GET['status'])) {
                $status = strtolower($xss->xss_clean($_GET['status']));
                if (array_key_exists($status, self::VALID_STATUS)) {
                    $request->setStatus(self::VALID_STATUS[$status]);
                }
            }

            // Start Date
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

            // End Date
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

            // GenreAnime
            if (isset($_GET['genre']) && \is_string($_GET['genre']) && strpos($_GET['genre'], ',')) {
                $_GET['genre'] = explode(',', $_GET['genre']);
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

            // Exclude genre passed for $_GET['genre']. Defaulted to false
            if (isset($_GET['genre_exclude'])) {
                $request->setGenreExclude(
                    (int) $_GET['genre_exclude'] === 1
                );
            }

            // Sort
            if (isset($_GET['sort'])) {
                $order = $xss->xss_clean($_GET['sort']);

                if (array_key_exists($order, self::VALID_SORT)) {
                    $request->setSort(self::VALID_SORT[$order]);
                }
            }
        }

        // Anime
        if ($request instanceof AnimeSearchRequest) {
            // Rating/Rated
            if (isset($_GET['rated'])) {
                $rated = strtolower($xss->xss_clean($_GET['rated']));
                if (array_key_exists($rated, self::VALID_RATING)) {
                    $request->setRated(self::VALID_RATING[$rated]);
                }
            }

            // Producer
            if (isset($_GET['producer'])) {
                $producer = (int) $_GET['producer'];

                $request->setProducer($producer);
            }

            // Order By
            if (isset($_GET['order_by'])) {
                $order = $xss->xss_clean($_GET['order_by']);

                if (array_key_exists($order, self::VALID_ANIME_ORDER_BY)) {
                    $request->setOrderBy(self::VALID_ANIME_ORDER_BY[$order]);
                }
            }
        }

        // Manga
        if ($request instanceof MangaSearchRequest) {
            // Magazine
            if (isset($_GET['magazine'])) {
                $producer = (int) $_GET['magazine'];

                $request->setMagazine($producer);
            }

            // Order By
            if (isset($_GET['order_by'])) {
                $order = $xss->xss_clean($_GET['order_by']);

                if (array_key_exists($order, self::VALID_MANGA_ORDER_BY)) {
                    $request->setOrderBy(self::VALID_MANGA_ORDER_BY[$order]);
                }
            }
        }

        // Users
        if ($request instanceof UserSearchRequest) {
            // Gender
            if (isset($_GET['gender'])) {
                $gender = $xss->xss_clean($_GET['gender']);

                if (array_key_exists($gender, self::VALID_GENDER)) {
                    $request->setGender(self::VALID_GENDER[$gender]);
                }
            }

            // Location
            if (isset($_GET['location'])) {
                $location = $xss->xss_clean($_GET['location']);

                $request->setLocation($location);
            }

            // Max Age
            if (isset($_GET['max-age'])) {
                $maxAge = (int) $_GET['max-age'];
                $request->setMaxAge($maxAge);
            }

            // Min Age
            if (isset($_GET['min-age'])) {
                $maxAge = (int) $_GET['min-age'];
                $request->setMaxAge($maxAge);
            }
        }

        return $request;
    }
}
