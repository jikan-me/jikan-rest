<?php

namespace App\Services;

interface ScoutSearchService
{
    /**
     * Executes a search operation via Laravel Scout on the provided model class.
     * @param object|string $modelClass
     * @param string $q
     * @return \Laravel\Scout\Builder
     */
    public function search(object|string $modelClass, string $q): \Laravel\Scout\Builder;
}
