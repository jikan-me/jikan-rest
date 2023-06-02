<?php

namespace App\Enums;


use Spatie\Enum\Laravel\Enum;
use Jikan\Helper\Constants as JikanConstants;

/**
 * @method static self title()
 * @method static self started_date()
 * @method static self score()
 * @method static self last_updated()
 * @method static self priority()
 * @method static self progress()
 * @method static self chapters_read()
 * @method static self volumes_read()
 * @method static self type()
 * @method static self publish_start()
 * @method static self publish_end()
 * @method static self status()
 */
final class UserMangaListOrderByEnum extends Enum
{
    // labels will be the values used for mapping, meanwhile the values are the names of the enum elements,
    // because these are getting passed in through the query string in requests, and we validate against them
    protected static function labels(): array
    {
        return [
            'title' => JikanConstants::USER_MANGA_LIST_ORDER_BY_TITLE,
            'finished_date' => JikanConstants::USER_MANGA_LIST_ORDER_BY_FINISHED_DATE,
            'started_date' => JikanConstants::USER_MANGA_LIST_ORDER_BY_STARTED_DATE,
            'score' => JikanConstants::USER_MANGA_LIST_ORDER_BY_SCORE,
            'last_updated' => JikanConstants::USER_MANGA_LIST_ORDER_BY_LAST_UPDATED,
            'priority' => JikanConstants::USER_MANGA_LIST_ORDER_BY_PRIORITY,
            'progress' => JikanConstants::USER_MANGA_LIST_ORDER_BY_CHAPTERS,
            'chapters_read' => JikanConstants::USER_MANGA_LIST_ORDER_BY_CHAPTERS,
            'volumes_read' => JikanConstants::USER_MANGA_LIST_ORDER_BY_VOLUMES,
            'type' => JikanConstants::USER_MANGA_LIST_ORDER_BY_TYPE,
            'publish_start' => JikanConstants::USER_MANGA_LIST_ORDER_BY_PUBLISH_START,
            'publish_end' => JikanConstants::USER_MANGA_LIST_ORDER_BY_PUBLISH_END,
            'status' => JikanConstants::USER_MANGA_LIST_ORDER_BY_STATUS,
        ];
    }
}
