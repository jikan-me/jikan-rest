<?php

namespace App\Providers;

use App\Http\QueryBuilder\SearchQueryBuilderService;

class SearchQueryBuilderProvider
{
    private array $searchQueryBuilders = [];

    public function __construct(array $searchQueryBuilders)
    {
        foreach($searchQueryBuilders as $searchQueryBuilder)
        {
            $this->searchQueryBuilders[$searchQueryBuilder->getIdentifier()] = $searchQueryBuilder;
        }
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function getQueryBuilder(string $name): SearchQueryBuilderService
    {
        if (!array_key_exists($name, $this->searchQueryBuilders))
        {
            throw new \InvalidArgumentException("Invalid argument: name.");
        }

        return $this->searchQueryBuilders[$name];
    }
}
