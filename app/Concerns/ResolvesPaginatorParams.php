<?php

namespace App\Concerns;

use Illuminate\Support\Facades\App;

trait ResolvesPaginatorParams
{
    private function getPaginatorParams(?int $limit = null, ?int $page = null): array
    {
        $default_max_results_per_page = max_results_per_page();
        $limit = $limit ?? $default_max_results_per_page;
        $page = $page ?? 1;

        return compact("limit", "page");
    }
}
