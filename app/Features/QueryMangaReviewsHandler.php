<?php

namespace App\Features;

use App\Dto\QueryMangaReviewsCommand;
use App\Enums\ReviewTypeEnum;

/**
 * @extends QueryReviewsHandler<QueryMangaReviewsCommand>
 */
final class QueryMangaReviewsHandler extends QueryReviewsHandler
{
    protected function reviewType(): ReviewTypeEnum
    {
        return ReviewTypeEnum::manga();
    }

    /**
     * @inheritDoc
     */
    public function requestClass(): string
    {
        return QueryMangaReviewsCommand::class;
    }
}
