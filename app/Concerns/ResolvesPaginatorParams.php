<?php

namespace App\Concerns;

trait ResolvesPaginatorParams
{
    private function getPaginatorParams(?int $limit = null, ?int $page = null): array
    {
        $default_max_results_per_page = env('MAX_RESULTS_PER_PAGE', 25);
        $limit = $limit ?? $default_max_results_per_page;
        $page = $page ?? 1;

        return compact($limit, $page);
    }
}
