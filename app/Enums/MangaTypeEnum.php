<?php

namespace App\Enums;

use Spatie\Enum\Laravel\Enum;

/**
 * @method static self manga()
 * @method static self novel()
 * @method static self lightnovel()
 * @method static self oneshot()
 * @method static self doujin()
 * @method static self manhwa()
 * @method static self manhua()
 *
 * @OA\Schema(
 *   schema="manga_search_query_type",
 *   description="Available Manga types",
 *   type="string",
 *   enum={"manga","novel", "lightnovel", "oneshot","doujin","manhwa","manhua"}
 * )
 */
final class MangaTypeEnum extends Enum
{
    protected static function labels(): array
    {
        return [
            'manga' => 'Manga',
            'novel' => 'Novel',
            'lightnovel' => 'Light Novel',
            'oneshot' => 'One-shot',
            'doujin' => 'Doujinshi',
            'manhwa' => 'Manhwa',
            'manhua' => 'Manhua'
        ];
    }
}
