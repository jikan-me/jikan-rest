<?php

namespace App\Services;

use Laravel\Scout\Builder;

interface ScoutSearchService
{
    /**
     * Executes a search operation via Laravel Scout on the provided model class.
     *
     * @param  object|string  $modelClass
     */
    public function search(string $q, ?string $orderByField = null,
        bool $sortDirectionDescending = false): Builder;
}
