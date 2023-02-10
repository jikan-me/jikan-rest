<?php

namespace App\Enums;


use Spatie\Enum\Laravel\Enum;
use Jikan\Helper\Constants as JikanConstants;

/**
 * @method static self title()
 * @method static self started_date()
 * @method static self score()
 * @method static self last_updated()
 * @method static self type()
 * @method static self rated()
 * @method static self rewatch_value()
 * @method static self priority()
 * @method static self episodes_watched()
 * @method static self storage()
 * @method static self air_start()
 * @method static self air_end()
 * @method static self status()
 */
final class UserAnimeListOrderByEnum extends Enum
{
    // labels will be the values used for mapping, meanwhile the values are the names of the enum elements,
    // because these are getting passed in through the query string in requests, and we validate against them
    protected static function labels(): array
    {
        return [
            'title' => JikanConstants::USER_ANIME_LIST_ORDER_BY_TITLE,
            'finished_date' => JikanConstants::USER_ANIME_LIST_ORDER_BY_FINISHED_DATE,
            'started_date' => JikanConstants::USER_ANIME_LIST_ORDER_BY_STARTED_DATE,
            'score' => JikanConstants::USER_ANIME_LIST_ORDER_BY_SCORE,
            'last_updated' => JikanConstants::USER_ANIME_LIST_ORDER_BY_LAST_UPDATED,
            'type' => JikanConstants::USER_ANIME_LIST_ORDER_BY_TYPE,
            'rated' => JikanConstants::USER_ANIME_LIST_ORDER_BY_RATED,
            'rewatch_value' => JikanConstants::USER_ANIME_LIST_ORDER_BY_REWATCH_VALUE,
            'priority' => JikanConstants::USER_ANIME_LIST_ORDER_BY_PRIORITY,
            'episodes_watched' => JikanConstants::USER_ANIME_LIST_ORDER_BY_PROGRESS,
            'storage' => JikanConstants::USER_ANIME_LIST_ORDER_BY_STORAGE,
            'air_start' => JikanConstants::USER_ANIME_LIST_ORDER_BY_AIR_START,
            'air_end' => JikanConstants::USER_ANIME_LIST_ORDER_BY_AIR_END,
            'status' => JikanConstants::USER_ANIME_LIST_ORDER_BY_STATUS,
        ];
    }
}
