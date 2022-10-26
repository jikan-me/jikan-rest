<?php

namespace App\Http\QueryBuilder;

use App\Character;
use Illuminate\Support\Collection;

class CharacterSearchQueryBuilder extends SearchQueryBuilder
{
    protected string $displayNameFieldName = "name";

    /**
     * @OA\Schema(
     *   schema="characters_search_query_orderby",
     *   description="Available Character order_by properties",
     *   type="string",
     *   enum={"mal_id", "name", "favorites"}
     * )
     */
    const ORDER_BY = [
        'mal_id' => 'mal_id',
        'name' => 'name',
        'favorites' => 'member_favorites'
    ];

    protected function getModelClass(): object|string
    {
        return Character::class;
    }

    protected function buildQuery(Collection $requestParameters, \Illuminate\Database\Eloquent\Builder|\Laravel\Scout\Builder $results): \Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder
    {
        return $results;
    }

    protected function getOrderByFieldMap(): array
    {
        return self::ORDER_BY;
    }

    public function getIdentifier(): string
    {
        return "character";
    }
}