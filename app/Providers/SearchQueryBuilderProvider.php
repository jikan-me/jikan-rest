<?php

namespace App\Providers;

use App\Http\QueryBuilder\SearchQueryBuilderService;
use Illuminate\Support\Facades\Log;

class SearchQueryBuilderProvider
{
    private array $searchQueryBuilders = [];

    public function __construct(\IteratorAggregate $searchQueryBuilders)
    {
        try {
            foreach ($searchQueryBuilders->getIterator() as $searchQueryBuilder) {
                $this->searchQueryBuilders[$searchQueryBuilder->getIdentifier()] = $searchQueryBuilder;
            }
        } catch (\Exception $e) {
            Log::error($e);
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
