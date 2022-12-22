<?php

namespace App\Services;

use Laravel\Scout\Builder;

interface ScoutSearchService
{
    /**
     * Executes a search operation via Laravel Scout on the provided model class.
     * @param object|string $modelClass
     * @param string $q
     * @param string|null $orderByField
     * @param bool $sortDirectionDescending
     * @return Builder
     */
    public function search(object|string $modelClass, string $q, ?string $orderByField = null,
                           bool $sortDirectionDescending = false): \Laravel\Scout\Builder;
}
