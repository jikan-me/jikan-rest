<?php

namespace App\Services;

use App\Helpers\Guards;

class DefaultScoutSearchService implements ScoutSearchService
{
    public function search(object|string $modelClass, string $q, ?string $orderByField = null,
                           bool $sortDirectionDescending = false): \Laravel\Scout\Builder
    {
        Guards::shouldBeMongoDbModel($modelClass);

        return $modelClass::search($q);
    }
}
