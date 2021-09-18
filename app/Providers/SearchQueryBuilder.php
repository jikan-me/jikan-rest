<?php

namespace App\Providers;

use Illuminate\Http\Request;
use Jikan\Model\Anime\Anime;
use Jikan\Request\Search\AnimeSearchRequest;
use Jikan\Request\Search\MangaSearchRequest;
use Jikan\Request\Search\PersonSearchRequest;
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

    public static function create(Request $request, $parserRequest)
    {
        $query = $request->get('q');
        $page = $request->get('page');
        $letter = $request->get('letter');
        $subtype = $request->get('type');
        $score = $request->get('score');
        $status = $request->get('status');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $genres = $request->get('genre');
        $genreExclude = $request->get('genre_exclude');
        $sort = $request->get('sort');
        $orderBy = $request->get('order_by');
        $magazine = $request->get('magazine');
        $producer = $request->get('producer');
        $rated = $request->get('rated');

        // Query
        if ($query !== null) {
            $parserRequest->setQuery($query);
        }

        // Page
        if ($page !== null) {
            $parserRequest->setPage((int)$page);
        }

        // Starts with glyph
        if (isset($_GET['letter'])) {
            $parserRequest->setStartsWithChar('');

            if (!empty($_GET['letter'])) {
                $letter =
                    // https://stackoverflow.com/questions/1972100/getting-the-first-character-of-a-string-with-str0#comment27161857_1972111
                    mb_substr($letter, 0, 1, 'utf-8');

                $parserRequest->setStartsWithChar($letter);
            }
        }

        // Anime & Manga
        if ($parserRequest instanceof AnimeSearchRequest || $parserRequest instanceof MangaSearchRequest) {
            // Type
            if ($subtype !== null) {
                $subtype = strtolower($subtype);
                if (array_key_exists($subtype, self::VALID_SUB_TYPES)) {
                    $parserRequest->setType(self::VALID_SUB_TYPES[$subtype]);
                }
            }

            // Score
            if ($score !== null) {
                $score = (float) $score;

                if ($score >= 0.0 && $score <= 10.0) {
                    $parserRequest->setScore($score);
                }
            }

            // Status
            if ($status !== null) {
                $status = strtolower($status);
                if (array_key_exists($status, self::VALID_STATUS)) {
                    $parserRequest->setStatus(self::VALID_STATUS[$status]);
                }
            }

            // Start Date
            if ($startDate) {
                if (preg_match("~[0-9]{4}-[0-9]{2}-[0-9]{2}~", $startDate)) {
                    $startDate = explode("-", $startDate);
                    $parserRequest->setStartDate(
                        (int) $startDate[2],
                        (int) $startDate[1],
                        (int) $startDate[0]
                    );
                }
            }

            // End Date
            if ($endDate) {
                if (preg_match("~[0-9]{4}-[0-9]{2}-[0-9]{2}~", $endDate)) {
                    $endDate = explode("-", $endDate);
                    $parserRequest->setEndDate(
                        (int) $endDate[2],
                        (int) $endDate[1],
                        (int) $endDate[0]
                    );
                }
            }

            // Genre
            if ($genres !== null && \is_string($genres) && strpos($genres, ',')) {
                $genres = explode(',', $genres);
            }

            if ($genres !== null) {
                if (\is_array($genres)) {
                    foreach ($genres as $genre) {
                        $genre = (int) $genre;

                        if ($genre >= self::VALID_MIN_GENRE && $genre <= self::VALID_MAX_GENRE) {
                            $parserRequest->setGenre($genre);
                        }
                    }
                }

                if (!\is_array($genres)) {
                    $genres = (int) $genres;

                    if ($genres >= self::VALID_MIN_GENRE && $genres <= self::VALID_MAX_GENRE) {
                        $parserRequest->setGenre($genres);
                    }
                }
            }

            // Exclude genre passed for $_GET['genre']. Defaulted to false
            if ($genreExclude) {
                $parserRequest->setGenreExclude(
                    ((int) $genreExclude == 1) ? true : false
                );
            }

            // Sort
            if ($sort !== null) {

                if (array_key_exists($sort, self::VALID_SORT)) {
                    $parserRequest->setSort(self::VALID_SORT[$sort]);
                }
            }
        }

        // Anime
        if ($parserRequest instanceof AnimeSearchRequest) {
            // Rating/Rated
            if ($rated !== null) {
                $rated = strtolower($rated);
                if (array_key_exists($rated, self::VALID_RATING)) {
                    $parserRequest->setRated(self::VALID_RATING[$rated]);
                }
            }

            // Producer
            if ($producer !== null) {
                $producer = (int) $producer;

                $parserRequest->setProducer($producer);
            }

            // Order By
            if ($orderBy) {

                if (array_key_exists($orderBy, self::VALID_ANIME_ORDER_BY)) {
                    $parserRequest->setOrderBy(self::VALID_ANIME_ORDER_BY[$orderBy]);
                }
            }
        }

        // Manga
        if ($parserRequest instanceof MangaSearchRequest) {
            // Magazine
            if ($magazine !== null) {
                $producer = (int) $magazine;

                $parserRequest->setMagazine($magazine);
            }

            // Order By
            if ($orderBy !== null) {

                if (array_key_exists($orderBy, self::VALID_MANGA_ORDER_BY)) {
                    $parserRequest->setOrderBy(self::VALID_MANGA_ORDER_BY[$orderBy]);
                }
            }
        }

        return $parserRequest;
    }
}
