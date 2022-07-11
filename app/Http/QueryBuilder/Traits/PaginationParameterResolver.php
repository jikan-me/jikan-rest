<?php

namespace App\Http\QueryBuilder\Traits;

use Illuminate\Http\Request;

trait PaginationParameterResolver
{
    private function getPaginateParameters(Request $request): array
    {
        $page = $request->get('page') ?? 1;
        $limit = $request->get('limit') ?? env('MAX_RESULTS_PER_PAGE', 25);

        $limit = (int)$limit;
        $page = (int)$page;

        if ($limit <= 0) {
            $limit = 1;
        }

        if ($limit > env('MAX_RESULTS_PER_PAGE', 25)) {
            $limit = env('MAX_RESULTS_PER_PAGE', 25);
        }

        if ($page <= 0) {
            $page = 1;
        }

        return compact("page", "limit");
    }
}
