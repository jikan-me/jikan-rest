<?php

namespace App\Features;

use App\Dto\QueryAnimeRecommendationsCommand;
use Jikan\Helper\Constants;

/**
 * @extends QueryRecommendationsHandler<QueryAnimeRecommendationsCommand>
 */
final class QueryAnimeRecommendationsHandler extends QueryRecommendationsHandler
{
    public function requestClass(): string
    {
        return QueryAnimeRecommendationsCommand::class;
    }

    protected function recommendationType(): string
    {
        return Constants::RECENT_RECOMMENDATION_ANIME;
    }
}
