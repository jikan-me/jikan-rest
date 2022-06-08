<?php

namespace App\Http\QueryBuilder;

use App\Anime;

class AnimeSearchQueryBuilder extends MediaSearchQueryBuilder
{
    protected array $parameterNames = ["producer", "producers", "rating"];
    protected string $displayNameFieldName = "title";

    /**
     * @OA\Schema(
     *   schema="anime_search_query_type",
     *   description="Available Anime types",
     *   type="string",
     *   enum={"tv","movie","ova","special","ona","music"}
     * )
     */
    const MAP_TYPES = [
        'tv' => 'TV',
        'movie' => 'Movie',
        'ova' => 'OVA',
        'special' => 'Special',
        'ona' => 'ONA',
        'music' => 'Music'
    ];

    /**
     * @OA\Schema(
     *   schema="anime_search_query_status",
     *   description="Available Anime statuses",
     *   type="string",
     *   enum={"airing","complete","upcoming"}
     * )
     */
    const MAP_STATUS = [
        'airing' => 'Currently Airing',
        'complete' => 'Finished Airing',
        'upcoming' => 'Not yet aired',
    ];

    /**
     * @OA\Schema(
     *   schema="anime_search_query_rating",
     *   description="Available Anime audience ratings<br><br><b>Ratings</b><br><ul><li>G - All Ages</li><li>PG - Children</li><li>PG-13 - Teens 13 or older</li><li>R - 17+ (violence & profanity)</li><li>R+ - Mild Nudity</li><li>Rx - Hentai</li></ul>",
     *   type="string",
     *   enum={"g","pg","pg13","r17","r","rx"}
     * )
     */
    const MAP_RATING = [
        'g' => 'G - All Ages',
        'pg' => 'PG - Children',
        'pg13' => 'PG-13 - Teens 13 or older',
        'r17' => 'R - 17+ (violence & profanity)',
        'r' => 'R+ - Mild Nudity',
        'rx' => 'Rx - Hentai'
    ];

    /**
     * @OA\Schema(
     *   schema="anime_search_query_orderby",
     *   description="Available Anime order_by properties",
     *   type="string",
     *   enum={"mal_id", "title", "type", "rating", "start_date", "end_date", "episodes", "score", "scored_by", "rank", "popularity", "members", "favorites" }
     * )
     */
    const ORDER_BY = [
        'start_date' => 'aired.from',
        'end_date' => 'aired.to',
        'episodes' => 'episodes',
        'rating' => 'rating',
        'type' => 'type',
    ];

    protected function buildQuery(array $requestParameters, \Illuminate\Database\Eloquent\Builder|\Laravel\Scout\Builder $results): \Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder
    {
        $builder = parent::buildQuery($requestParameters, $results);
        extract($requestParameters);

        if (!is_null($rating)) {
            $builder = $builder->where('rating', $this->mapRating($rating));
        }

        if (!is_null($producer)) $producers = $producer;

        if (!is_null($producers)) {
            $producers = explode(',', $producers);

            foreach ($producers as $producer) {
                if (empty($producer)) {
                    continue;
                }

                $producer = (int)$producer;

                // todo: this might be wrong, because the filtering like this should only happen in mongodb maybe?
                // https://laravel.com/docs/9.x/scout#customizing-the-eloquent-results-query
                $results = $results
                    ->query(fn($query) => $query
                        ->orWhere('producers.mal_id', $producer)
                        ->orWhere('licensors.mal_id', $producer)
                        ->orWhere('studios.mal_id', $producer));
            }
        }

        return $builder;
    }

    protected function filterByStartDate(\Illuminate\Database\Eloquent\Builder|\Laravel\Scout\Builder $builder, string $startDate): \Illuminate\Database\Eloquent\Builder|\Laravel\Scout\Builder
    {
        return $builder->where('aired.from', '>=', $startDate);
    }

    protected function filterByEndDate(\Illuminate\Database\Eloquent\Builder|\Laravel\Scout\Builder $builder, string $endDate): \Illuminate\Database\Eloquent\Builder|\Laravel\Scout\Builder
    {
        return $builder->where('aired.to', '<=', $endDate);
    }

    protected function getStatusMap(): array
    {
        return self::MAP_STATUS;
    }

    protected function getTypeMap(): array
    {
        return self::MAP_TYPES;
    }

    protected function getModelClass(): object|string
    {
        return Anime::class;
    }

    protected function getOrderByFieldMap(): array
    {
        $map = parent::getOrderByFieldMap();
        return array_merge($map, self::ORDER_BY);
    }

    protected function getAdultRating(): string
    {
        return self::MAP_RATING['rx'];
    }

    public function getIdentifier(): string
    {
        return "anime";
    }

    protected function mapRating(?string $rating = null): ?string
    {
        $rating = strtolower($rating);

        return self::MAP_RATING[$rating] ?? null;
    }
}
