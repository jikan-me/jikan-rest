<?php

namespace App\Enums;


use Spatie\Enum\Laravel\Enum;
use Jikan\Helper\Constants as JikanConstants;

/**
 * @method static self airing()
 * @method static self finished()
 * @method static self complete()
 * @method static self to_be_aired()
 * @method static self not_yet_aired()
 * @method static self tba()
 * @method static self nya()
 */
final class AnimeListAiringStatusFilterEnum extends Enum
{
    protected static function labels()
    {
        return [
            'airing' => JikanConstants::USER_ANIME_LIST_CURRENTLY_AIRING,
            'finished' => JikanConstants::USER_ANIME_LIST_FINISHED_AIRING,
            'complete' => JikanConstants::USER_ANIME_LIST_FINISHED_AIRING,
            'to_be_aired' => JikanConstants::USER_ANIME_LIST_NOT_YET_AIRED,
            'not_yet_aired' => JikanConstants::USER_ANIME_LIST_NOT_YET_AIRED,
            'tba' => JikanConstants::USER_ANIME_LIST_NOT_YET_AIRED,
            'nya' => JikanConstants::USER_ANIME_LIST_NOT_YET_AIRED,
        ];
    }
}
