<?php

namespace App\Enums;

use Spatie\Enum\Laravel\Enum;

/**
 * @method static self publishing()
 * @method static self complete()
 * @method static self hiatus()
 * @method static self discontinued()
 * @method static self upcoming()
 *
 * @OA\Schema(
 *   schema="manga_search_query_status",
 *   description="Available Manga statuses",
 *   type="string",
 *   enum={"publishing","complete","hiatus","discontinued","upcoming"}
 * )
 */
final class MangaStatusEnum extends Enum
{
    protected static function labels(): array
    {
        return [
            "publishing" => "Publishing",
            "complete" => "Finished",
            "hiatus" => "On Hiatus",
            "discontinued" => "Discontinued",
            "upcoming" => "Not yet published"
        ];
    }
}
