<?php

namespace App\Http\QueryBuilder;

use App\Manga;

class MangaSearchQueryBuilder extends MediaSearchQueryBuilder
{
    protected array $parameterNames = ["magazine", "magazines", "rating"];
    protected string $displayNameFieldName = "title";

    /**
     * @OA\Schema(
     *   schema="manga_search_query_type",
     *   description="Available Manga types",
     *   type="string",
     *   enum={"manga","novel", "lightnovel", "oneshot","doujin","manhwa","manhua"}
     * )
     */
    const MAP_TYPES = [
        'manga' => 'Manga',
        'novel' => 'Novel',
        'lightnovel' => 'Light Novel',
        'oneshot' => 'One-shot',
        'doujin' => 'Doujinshi',
        'manhwa' => 'Manhwa',
        'manhua' => 'Manhua'
    ];

    /**
     * @OA\Schema(
     *   schema="manga_search_query_status",
     *   description="Available Manga statuses",
     *   type="string",
     *   enum={"publishing","complete","hiatus","discontinued","upcoming"}
     * )
     */
    const MAP_STATUS = [
        'publishing' => 'Publishing',
        'complete' => 'Finished',
        'hiatus' => 'On Hiatus',
        'discontinued' => 'Discontinued',
        'upcoming' => 'Not yet published'
    ];

    /**
     * @OA\Schema(
     *   schema="manga_search_query_orderby",
     *   description="Available Manga order_by properties",
     *   type="string",
     *   enum={"mal_id", "title", "start_date", "end_date", "chapters", "volumes", "score", "scored_by", "rank", "popularity", "members", "favorites"}
     * )
     */
    const ORDER_BY = [
        'chapters' => 'chapters',
        'volumes' => 'volumes',
        'start_date' => 'published.from',
        'end_date' => 'published.to',
    ];

    protected function buildQuery(array $requestParameters, \Illuminate\Database\Eloquent\Builder|\Laravel\Scout\Builder $results): \Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder
    {
        $builder = parent::buildQuery($requestParameters, $results);
        $magazine = $requestParameters['magazine'];
        $magazines = $requestParameters['magazines'];

        if (!is_null($magazine)) $magazines = $magazine;

        if (!is_null($magazines)) {
            $magazines = explode(',', $magazines);

            foreach ($magazines as $magazine) {
                if (empty($magazine)) {
                    continue;
                }

                $magazine = (int)$magazine;

                $builder = $builder
                    ->orWhere('serializations.mal_id', $magazine);
            }
        }

        return $builder;
    }

    protected function filterByStartDate(\Illuminate\Database\Eloquent\Builder|\Laravel\Scout\Builder $builder, string $startDate): \Illuminate\Database\Eloquent\Builder|\Laravel\Scout\Builder
    {
        return $builder->where('published.from', '>=', $startDate);
    }

    protected function filterByEndDate(\Illuminate\Database\Eloquent\Builder|\Laravel\Scout\Builder $builder, string $endDate): \Illuminate\Database\Eloquent\Builder|\Laravel\Scout\Builder
    {
        return $builder->where('published.to', '<=', $endDate);
    }

    protected function getStatusMap(): array
    {
        return self::MAP_STATUS;
    }

    protected function getTypeMap(): array
    {
        return self::MAP_TYPES;
    }

    protected function getAdultRating(): string
    {
        return "Doujinshi";
    }

    protected function getModelClass(): object|string
    {
        return Manga::class;
    }

    protected function getOrderByFieldMap(): array
    {
        $map = parent::getOrderByFieldMap();
        return array_merge($map, self::ORDER_BY);
    }

    function getIdentifier(): string
    {
        return "manga";
    }
}