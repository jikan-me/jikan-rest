<?php

namespace App\Http\Controllers;

use Jikan\Jikan;
use Jikan\Request\Search\AnimeSearchRequest;
use Jikan\Request\Search\MangaSearchRequest;
use Jikan\Request\Search\CharacterSearchRequest;
use Jikan\Request\Search\PersonSearchRequest;
use Jikan\Helper\Constants as JikanConstants;

class TopController extends Controller
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
    private $validRating = [
        'g' => JikanConstants::SEARCH_ANIME_RATING_G,
        'pg' => JikanConstants::SEARCH_ANIME_PG,
        'pg13' => JikanConstants::SEARCH_ANIME_PG13,
        'r17' => JikanConstants::SEARCH_ANIME_R17,
        'r' => JikanConstants::SEARCH_ANIME_R,
        'rx' => JikanConstants::SEARCH_ANIME_RX
    ];

    private const VALID_GENRE_ANIME = [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43];
    private const VALID_GENRE_MANGA = [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45];


    public function anime(int $page = 1) {

    }

    public function manga(int $page = 1) {

    }

    public function people(int $page = 1) {

    }

    public function character(int $page = 1) {

    }

}

/*
 * make factory?
    private function buildConfig() {
        $antiXss = new \voku\helper\AntiXSS();
        
        if (!isset($_GET)) {return;}

        if (isset($_GET['type'])) {
            $subtype = strtolower($antiXss->xss_clean($_GET['type']));
            if (array_key_exists($subtype, $this->validSubTypes)) {
                $this->config['Type'] = $this->validSubTypes[$subtype];
            }
        }

        if (isset($_GET['score'])) {
            $this->config['Score'] = (float) $_GET['score'];
        }

        if (isset($_GET['status'])) {
            $status = strtolower($antiXss->xss_clean($_GET['status']));
            if (array_key_exists($status, $this->validStatus)) {
                $this->config['Status'] = $this->validStatus[$status];
            }
        }

        if (isset($_GET['rated'])) {
            $rated = strtolower($antiXss->xss_clean($_GET['rated']));
            if (array_key_exists($rated, $this->validRating)) {
                $this->config['Rated'] = $this->validRating[$rated];
            }
        }

        if (isset($_GET['start_date'])) {
            $startDate = $antiXss->xss_clean($_GET['start_date']);
            if (preg_match("~[0-9]{4}-[0-9]{2}-[0-9]{2}~", $startDate)) {
                $this->config['StartDate'] = explode("-", $startDate);
            }
        }

        if (isset($_GET['end_date'])) {
            $endDate = $antiXss->xss_clean($_GET['end_date']);
            if (preg_match("~[0-9]{4}-[0-9]{2}-[0-9]{2}~", $endDate)) {
                $this->config['EndDate'] = explode("-", $endDate);
            }
        }

        if (isset($_GET['genre'])) {

            $this->config['Genre'] = [];

            if (is_array($_GET['genre'])) {
                foreach ($_GET['genre'] as $genre) {
                    $genre = (int) $genre;

                    if (in_array($genre, $this->validGenre)) {
                        $this->config['Genre'][] = $genre;
                    }
                }
            } else {
                $genre = (int) $_GET['genre'];
                if (in_array($genre, $this->validGenre)) {
                    $this->config['Genre'][] = $genre;
                }
            }
        }

        if (isset($_GET['genre_exclude'])) {
            $this->config['GenreInclude'] = ((int) $_GET['genre_exclude'] == 1) ? false : true;
        }
    }

    // this method is just for hashing and differs from the request URL
    private function configToString() {
        $url = "?";
        foreach ($this->config as $key => $value) {
            if (is_array($value)) {
                $value = implode(",", $value);
            }

            $url .= $key . "=" . $value . "&";
        }

        return $url;
    }

*/