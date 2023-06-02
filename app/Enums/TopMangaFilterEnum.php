<?php

namespace App\Enums;

use Spatie\Enum\Laravel\Enum;

/**
 * @method static self publishing()
 * @method static self upcoming()
 * @method static self bypopularity()
 * @method static self favorite()
 *
 * @OA\Schema(
 *   schema="top_manga_filter",
 *   description="Top items filter types",
 *   type="string",
 *   enum={"publishing","upcoming","bypopularity","favorite"}
 * )
 */
final class TopMangaFilterEnum extends Enum
{
}
