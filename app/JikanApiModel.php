<?php

namespace App;

use App\Enums\AnimeRatingEnum;
use App\Enums\MangaTypeEnum;
use App\Filters\FilterQueryString;
use Illuminate\Support\Collection;
use Jenssegers\Mongodb\Eloquent\Builder;
use Jikan\Helper\Constants;

class JikanApiModel extends \Jenssegers\Mongodb\Eloquent\Model
{
    use FilterQueryString;

    /**
     * The list of parameters which can be used to filter the result-set from the database.
     * The available field names and "order_by" is allowed as values. If "order_by" is specified then the field name
     * from the  "order_by" query string parameter will be used to sort the results.
     * @var string[]
     */
    protected array $filters = [];

    /** @noinspection PhpUnused */
    public function scopeRandom(Builder $query, int $numberOfRandomItems = 1, bool $sfw = false, bool $unapproved = false): Collection
    {
        return $query->raw(function(\Jenssegers\Mongodb\Collection $collection) use ($numberOfRandomItems, $sfw, $unapproved) {
            $sfwFilter = [
                'demographics.mal_id' => [
                    '$nin' => [
                        Constants::GENRE_ANIME_HENTAI,
                        Constants::GENRE_ANIME_EROTICA,
                        Constants::GENRE_MANGA_HENTAI,
                        Constants::GENRE_MANGA_EROTICA
                    ]
                ],
                'rating' => ['$ne' => AnimeRatingEnum::rx()->label],
                'type' => ['$ne' => MangaTypeEnum::doujin()->label],
                'genres.mal_id' => ['$nin' => [
                    Constants::GENRE_ANIME_HENTAI,
                    Constants::GENRE_MANGA_HENTAI
                ]]
            ];

            $pipelineParams = [
                ['$sample' => ['size' => $numberOfRandomItems]]
            ];

            if ($sfw && $unapproved) {
                array_unshift($pipelineParams, [
                    '$match' => [
                        ...$sfwFilter,
                        'approved' => false
                    ]
                ]);
            } else if ($sfw) {
                array_unshift($pipelineParams, ['$match' => $sfwFilter]);
            } else if ($unapproved) {
                array_unshift($pipelineParams, ['$match' => ['approved' => false]]);
            }

            return $collection->aggregate($pipelineParams);
        });
    }
}
