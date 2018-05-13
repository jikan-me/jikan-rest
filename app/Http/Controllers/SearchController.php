<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exceptions\Handler as Handler;
use Jikan;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Lazer\Classes\Database as Lazer;

class SearchController extends Controller
{
    /**
     * Create a new controller instance.
     *build
     * @return void
     */

    public $type;
    public $query;
    public $page;
    public $config = [];
    public $configObj;

    private $validTypes = ['anime', 'manga', 'character', 'person', 'people'];
    private $validSubTypes = [
        'tv' => 1,
        'ova' => 2,
        'movie' => 3,
        'special' => 4,
        'ona' => 5,
        'music' => 6,
        'manga' => 1,
        'novel' => 2,
        'oneshot' => 3,
        'doujin' => 4,
        'manhwa' => 5,
        'manhua' => 6
    ];
    private $validStatus = [
        'airing' => 1,
        'completed' => 2,
        'complete' => 2,
        'tba' => 3,
        'upcoming' => 3
    ];
    private $validRating = [
        'g' => 1,
        'pg' => 2,
        'pg13' => 3,
        'r17' => 4,
        'r' => 5,
        'rx' => 6
    ];
    private $validGenre = [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44];

    public function request($type = null, $query = null, $page = 1) {

        $antiXss = new \voku\helper\AntiXSS();

        $this->type = $type;
        $this->query = urlencode($antiXss->xss_clean($query));
        $this->page = $page;

        $jikan = new \Jikan\Jikan;
        
        if ($type == 'anime' || $type == 'manga') {
            $this->buildConfig();

            if (!empty($this->config)) {
                $this->configObj = new \Jikan\Helper\SearchConfig($type);

                foreach ($this->config as $key => $value) {
                    if (is_array($value)) {
                        $this->configObj->{"set".$key}(...$value);
                    } else {
                        $this->configObj->{"set".$key}($value);
                    }
                }
            }
        }

        $this->hash = sha1('search' . $this->type . $this->query . $this->page . $this->configToString());
        $this->response['request_hash'] = $this->hash;
        $this->response['request_cached'] = false;

        if (app('redis')->exists($this->hash)) {
            $this->response['request_cached'] = true;
            return response()->json(
                $this->response + json_decode(app('redis')->get($this->hash), true)
            );
        }


        if (!in_array($this->type, $this->validTypes)) {
            return response()->json(
                ['error' => 'Invalid type request: "' . $this->type . '"'], 400
            );
        }


        switch ($this->type) {
            case 'anime':
                try {

                    if (!empty($this->config)) {
                        $jikan->Search($this->query, ANIME, $this->page, $this->configObj);
                    } else {
                        $jikan->Search($this->query, ANIME, $this->page);
                    }

                } catch (\Exception $e) {
                    Bugsnag::notifyException($e);
                    return response()->json(
                        ['error' => $e->getMessage()], 404
                    );
                }
                break;
            case 'manga':
                try {

                    if (!empty($this->config)) {
                        $jikan->Search($this->query, MANGA, $this->page, $this->configObj);
                    } else {
                        $jikan->Search($this->query, MANGA, $this->page);
                    }

                } catch (\Exception $e) {
                    Bugsnag::notifyException($e);
                    return response()->json(
                        ['error' => $e->getMessage()], 404
                    );
                }
                break;
            case 'person':
            case 'people':
                try {

                    $jikan->Search($this->query, PERSON, $this->page);

                } catch (\Exception $e) {
                    Bugsnag::notifyException($e);
                    return response()->json(
                        ['error' => $e->getMessage()], 404
                    );
                }
                break;
            case 'character':
                try {

                    $jikan->Search($this->query, CHARACTER, $this->page);


                } catch (\Exception $e) {
                    Bugsnag::notifyException($e);
                    return response()->json(
                        ['error' => $e->getMessage()], 404
                    );
                }
                break;
        }

        if (empty($jikan->response) || $jikan->response === false) {
            return response()->json(['error' => 'MyAnimeList Rate Limiting reached. Slow down!'], 429);
        }

        $this->cache = json_encode($jikan->response);
        if ($this->cache !== false) {
            if (app('redis')->set($this->hash, $this->cache)) {
                app('redis')->expire($this->hash, CACHE_EXPIRE_SEARCH);
            }
        }

        return response()->json(
            $this->response + $jikan->response
        );
    }

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
            if (!is_array($_GET['genre'])) {
                return response()->json(
                    ['error' => 'Bad genre parse: "' . $this->type . '"'], 400
                );
            }

            $this->config['genre'] = [];
            foreach ($_GET['genre'] as $genre) {
                $genre = (int) $genre;

                if (in_array($genre, $this->validGenre)) {
                    $this->config['genre'][] = $genre;
                }
            }
        }

        if (isset($_GET['genre_exclude'])) {
            $this->config['GenreInclude'] = ((int) $_GET['genre_exclude'] == 1) ? false : true;
        }
    }

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

}
