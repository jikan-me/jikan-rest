<?php

namespace App\Features;

use App\Dto\QueryMangaRecommendationsCommand;
use Jikan\Helper\Constants;

/**
 * @extends QueryRecommendationsHandler<QueryMangaRecommendationsCommand>
 */
final class QueryMangaRecommendationsHandler extends QueryRecommendationsHandler
{
    protected function recommendationType(): string
    {
        return Constants::RECENT_RECOMMENDATION_MANGA;
    }

    /**
     * @inheritDoc
     */
    public function requestClass(): string
    {
        return QueryMangaRecommendationsCommand::class;
    }
}
