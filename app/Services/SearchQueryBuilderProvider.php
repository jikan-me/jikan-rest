<?php

namespace App\Services;

interface SearchQueryBuilderProvider
{
    public function getSearchQueryBuilder(EndpointType $recordType): QueryBuilderService;
}
