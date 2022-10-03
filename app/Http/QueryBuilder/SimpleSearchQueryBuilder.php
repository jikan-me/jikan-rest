<?php

namespace App\Http\QueryBuilder;

use App\GenreAnime;
use App\GenreManga;
use App\Magazine;
use App\Producers;
use App\Services\ScoutSearchService;
use Illuminate\Support\Collection;

class SimpleSearchQueryBuilder extends SearchQueryBuilder
{
    private string|object $modelClass;
    private string $identifier;
    private array $orderByFields;

    const ORDER_BY = [
        'mal_id', 'name', 'count'
    ];

    public function __construct(string $identifier, string|object $modelClass, bool $searchIndexesEnabled,
                                ScoutSearchService $scoutSearchService, array $orderByFields = self::ORDER_BY)
    {
        if (!in_array($modelClass, [GenreAnime::class, GenreManga::class, Producers::class, Magazine::class])) {
            throw new \InvalidArgumentException("Not supported model class has been provided.");
        }
        parent::__construct($searchIndexesEnabled, $scoutSearchService);
        $this->modelClass = $modelClass;
        $this->identifier = $identifier;
        $this->orderByFields = $orderByFields;
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
        return $this->orderByFields;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }
}
