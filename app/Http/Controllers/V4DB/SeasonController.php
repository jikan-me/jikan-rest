<?php

namespace App\Http\Controllers\V4DB;

use App\Anime;
use App\Http\HttpResponse;
use App\Http\QueryBuilder\SearchQueryBuilderAnime;
use App\Http\Resources\V4\AnimeCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jikan\Request\Seasonal\SeasonalRequest;
use Jikan\Request\SeasonList\SeasonListRequest;

class SeasonController extends Controller
{
    private const VALID_SEASONS = [
        'Summer',
        'Spring',
        'Winter',
        'Fall'
    ];

    private $request;
    private $season;
    private $year;

    public function main(Request $request, ?int $year = null, ?string $season = null)
    {
        $this->request = $request;

        if (!is_null($season)) {
            $this->season = ucfirst(
                strtolower($season)
            );
        }

        if (!is_null($year)) {
            $this->year = (int) $year;
        }

        if (!is_null($this->season)
        && !\in_array($this->season, self::VALID_SEASONS)) {
            return HttpResponse::badRequest($this->request);
        }

        if (is_null($season) && is_null($year)) {
            list($this->season, $this->year) = $this->getSeasonStr();
        }

        $results = Anime::query()
            ->where('premiered', "{$this->season} $this->year")
            ->orderBy('members', 'desc')
            ->get();

        return new AnimeCollection(
            $results
        );
    }

    public function archive()
    {
        return response(
            $this->serializer->serialize(
                ['data' => $this->jikan->getSeasonList(new SeasonListRequest())],
                'json'
            )
        );
    }

    public function later(Request $request)
    {
        $this->request = $request;

        $nextYear =   (new \DateTime(null, new \DateTimeZone('Asia/Tokyo')))
            ->modify('+1 year')
            ->format('Y');

        $results = Anime::query()
            ->where('status', 'Not yet aired')
            ->where('premiered', 'like', "%{$nextYear}%")
            ->orderBy('members', 'desc')
            ->get();

        $this->season = 'Later';

        return new AnimeCollection(
            $results
        );
    }

    private function getSeasonStr() : array
    {
        $date = new \DateTime(null, new \DateTimeZone('Asia/Tokyo'));

        $year = (int) $date->format('Y');
        $month = (int) $date->format('n');

        switch ($month) {
            case \in_array($month, range(1, 3)):
                return ['Winter', $year];
            case \in_array($month, range(4, 6)):
                return ['Spring', $year];
            case \in_array($month, range(7, 9)):
                return ['Summer', $year];
            case \in_array($month, range(10, 12)):
                return ['Fall', $year];
            default: throw new \Exception('Could not generate seasonal string');
        }
    }
}
