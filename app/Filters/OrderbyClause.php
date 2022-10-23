<?php
namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class OrderbyClause extends BaseClause
{

    protected function apply($query): Builder
    {
        foreach ($this->normalizeValues() as $field => $order) {
            $query->orderBy($field, $order);
        }

        return $query;
    }

    protected function validate($value): bool
    {
        return !in_array(null, (array)$value);
    }

    private function normalizeValues(): array
    {
        $normalized = [];

        foreach ((array)$this->values as $value) {

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
