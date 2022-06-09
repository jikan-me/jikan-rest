<?php

namespace App\Http\QueryBuilder;

use App\GenreAnime;
use App\GenreManga;
use App\Magazine;
use App\Producer;
use Illuminate\Support\Collection;

class SimpleSearchQueryBuilder extends SearchQueryBuilder
{
    private string|object $modelClass;
    private string $identifier;

    const ORDER_BY = [
        'mal_id', 'name', 'count'
    ];

    public function __construct(string $identifier, string|object $modelClass, bool $searchIndexesEnabled)
    {
        if (!in_array($modelClass, [GenreAnime::class, GenreManga::class, Producer::class, Magazine::class])) {
            throw new \InvalidArgumentException("Not supported model class has been provided.");
        }
        parent::__construct($searchIndexesEnabled);
        $this->modelClass = $modelClass;
        $this->identifier = $identifier;
    }

    protected function getModelClass(): object|string
    {
        return $this->modelClass;
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
        return $this->identifier;
    }
}