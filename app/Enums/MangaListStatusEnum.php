<?php

namespace App\Enums;


use Spatie\Enum\Laravel\Enum;

/**
 * @method static self all()
 * @method static self reading()
 * @method static self completed()
 * @method static self onhold()
 * @method static self dropped()
 * @method static self plantoread()
 *
 * @OA\Schema(
 *   schema="user_manga_list_status_filter",
 *   description="User's anime list status filter options",
 *   type="string",
 *   enum={"all", "reading", "completed", "onhold", "dropped", "plantoread"}
 * )
 */
final class MangaListStatusEnum extends Enum
{
    // labels will be the values used for mapping, meanwhile the values are the names of the enum elements,
    // because these are getting passed in through the query string in requests, and we validate against them
    protected static function labels(): array
    {
        return [
            "all" => "7",
            "reading" => "1",
            "completed" => "2",
            "onhold" => "3",
            "dropped" => "4",
            "plantoread" => "6"
        ];
    }
}
