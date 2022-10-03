<?php

namespace App\Http\QueryBuilder\Traits;

use Illuminate\Http\Request;

trait PaginationParameterResolver
{
    private function getPaginateParameters(Request $request): array
    {
        $page = $request->get('page') ?? 1;
        $default_max_results_per_page = env('MAX_RESULTS_PER_PAGE', 25);
        $class_vars = get_class_vars(get_class($this));
        // override on class basis
        if (array_key_exists('MAX_RESULTS_PER_PAGE', $class_vars)) {
            $default_max_results_per_page = $class_vars['MAX_RESULTS_PER_PAGE'];
        }
        $limit = $request->get('limit') ?? $default_max_results_per_page;

        $limit = (int)$limit;
        $page = (int)$page;

        if ($limit <= 0) {
            $limit = 1;
        }

        if ($limit > $default_max_results_per_page) {
            $limit = $default_max_results_per_page;
        }

        if ($page <= 0) {
            $page = 1;
        }

        return compact("page", "limit");
    }
}
