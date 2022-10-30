<?php

namespace App\Http\QueryBuilder\Scraper;

class QueryResolver
{
    /**
     * @var array
     */
    private array $queries = [];

    /**
     * @param Query $query
     * @return $this
     */
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
