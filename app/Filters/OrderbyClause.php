<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class OrderbyClause extends BaseClause
{
    /**
     * @return Builder<Model>
     */
    protected function apply($query): Builder
    {
        foreach ($this->normalizeValues() as $field => $order) {
            $query->orderBy($field, $order);
        }

        return $query;
    }

    protected function validate(mixed $value): bool
    {
        return !in_array(null, (array) $value);
    }

    /**
     * @return array<string, 'asc'|'desc'>
     */
    private function normalizeValues(): array
    {
        $normalized = [];

        foreach ((array) $this->values as $value) {

            $exploded = explode(',', $value);

            if (!empty($exploded[1]) and in_array($exploded[1], ['asc', 'desc'])) {
                $normalized[$exploded[0]] = $exploded[1];

                continue;
            }

            $normalized[$exploded[0]] = 'asc';
        }

        return $normalized;
    }
}
