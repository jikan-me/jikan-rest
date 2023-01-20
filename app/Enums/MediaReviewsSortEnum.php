<?php

namespace App\Enums;

use Jikan\Helper\Constants;
use Spatie\Enum\Laravel\Enum;

/**
 * @method static self mostVoted()
 * @method static self newest()
 * @method static self oldest()
 */
final class MediaReviewsSortEnum extends Enum
{
    protected static function labels(): array
    {
        return [
            "mostVoted" => Constants::REVIEWS_SORT_MOST_VOTED,
            "newest" => Constants::REVIEWS_SORT_NEWEST,
            "oldest" => Constants::REVIEWS_SORT_OLDEST
        ];
    }
}
