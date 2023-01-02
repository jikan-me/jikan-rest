<?php

namespace App\Services;

interface SearchQueryBuilderProvider
{
    function getSearchQueryBuilder(EndpointType $recordType): QueryBuilderService;
}
