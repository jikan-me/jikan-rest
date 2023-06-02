<?php

namespace App\Features;

use App\Dto\QueryAnimeReviewsCommand;
use App\Enums\ReviewTypeEnum;

/**
 * @extends QueryReviewsHandler<QueryAnimeReviewsCommand>
 */
final class QueryAnimeReviewsHandler extends QueryReviewsHandler
{
    protected function reviewType(): ReviewTypeEnum
    {
        return ReviewTypeEnum::anime();
    }

    /**
     * @inheritDoc
     */
    public function requestClass(): string
    {
        return QueryAnimeReviewsCommand::class;
    }
}
