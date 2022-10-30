<?php

namespace App\Http\QueryBuilder\Scraper;

class QueryResolver
{
    private array $queries = [];

    public function __construct() {
        return $this;
    }

    public function setNewQuery(Query $query): self
    {
        $this->queries[] = $query;
        return $this;
    }

    /**
     * @return array
     */
    public function getQueryValuesAsArray(): array
    {
        $values = [];
        foreach ($this->queries as $query) {
            $values[] = $query->getValue();
        }
        return $values;
    }
}
