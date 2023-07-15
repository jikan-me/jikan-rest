<?php

namespace App\Dto;

use App\Dto\Concerns\HasPreliminaryParameter;
use App\Dto\Concerns\HasSpoilersParameter;

final class QueryMangaReviewsCommand extends QueryReviewsCommand
{
    use HasPreliminaryParameter, HasSpoilersParameter;
}
