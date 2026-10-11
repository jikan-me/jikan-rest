<?php

namespace App\Features\Concerns;

use App\Enums\MediaReviewsSortEnum;
use Illuminate\Support\Collection;

trait ResolvesMediaReviewParams
{
    /**
     * @param Collection<array-key, mixed> $requestParams
     * @return array<string, mixed>
     */
    protected function getReviewRequestParams(Collection $requestParams): array
    {
        $id = $requestParams->get("id");
        // Note: `?? default` (not get($k, $default)) because a nullable DTO property
        // is present with a null value, which would otherwise override the default.
        $sort = $requestParams->get("sort") ?? MediaReviewsSortEnum::mostVoted()->value;
        $spoilers = $requestParams->get("spoilers") ?? false;
        $preliminary = $requestParams->get("preliminary") ?? false;
        $page = $requestParams->get("page") ?? 1;

        return compact("id", "sort", "spoilers", "preliminary", "page");
    }
}
