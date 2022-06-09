<?php

namespace App\Http\QueryBuilder;

use App\Person;
use Illuminate\Support\Collection;

class PeopleSearchQueryBuilder extends SearchQueryBuilder
{
    protected string $displayNameFieldName = "name";

    /**
     * @OA\Schema(
     *   schema="people_search_query_orderby",
     *   description="Available People order_by properties",
     *   type="string",
     *   enum={"mal_id", "name", "birthday", "favorites"}
     * )
     */
    const ORDER_BY = [
        'mal_id' => 'mal_id',
        'name' => 'name',
        'birthday' => 'birthday',
        'favorites' => 'member_favorites'
    ];

    protected function getModelClass(): object|string
    {
        return Person::class;
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
        return "people";
    }
}