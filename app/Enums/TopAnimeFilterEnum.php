<?php

namespace App\Enums;

use Spatie\Enum\Laravel\Enum;

/**
 * @method static self airing()
 * @method static self upcoming()
 * @method static self bypopularity()
 * @method static self favorite()
 *
 * @OA\Schema(
 *   schema="top_anime_filter",
 *   description="Top items filter types",
 *   type="string",
 *   enum={"airing","upcoming","bypopularity","favorite"}
 * )
 */
final class TopAnimeFilterEnum extends Enum
{
}
