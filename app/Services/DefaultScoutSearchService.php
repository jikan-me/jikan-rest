<?php

namespace App\Services;

use App\Helpers\Guards;

class DefaultScoutSearchService implements ScoutSearchService
{
    public function search(object|string $modelClass, string $q): \Laravel\Scout\Builder
    {
        Guards::shouldBeMongoDbModel($modelClass);

        return $modelClass::search($q);
    }
}
