<?php

namespace App\Services;

enum EndpointType
{
    case Anime;
    case Manga;
    case Character;
    case Person;
    case Club;
    case Producer;
    case User;
    case TopAnime;
    case TopManga;

    public function getSearchQueryBuilder(): QueryBuilderService
    {
        return app(SearchQueryBuilderProvider::class)->getSearchQueryBuilder($this);
    }
}
