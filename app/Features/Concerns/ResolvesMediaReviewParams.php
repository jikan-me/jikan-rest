<?php

namespace App\Features\Concerns;

use App\Enums\MediaReviewsSortEnum;
use Illuminate\Support\Collection;

trait ResolvesMediaReviewParams
{
    protected function getReviewRequestParams(Collection $requestParams): array
    {
        $id = $requestParams->get("id");
        $sort = $requestParams->get("sort", MediaReviewsSortEnum::mostVoted()->value);
        $spoilers = $requestParams->get("spoilers", false);
        $preliminary = $requestParams->get("preliminary", false);

        return compact($id, $sort, $spoilers, $preliminary);
    }
}
