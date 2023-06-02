<?php

namespace App\Enums;


use Spatie\Enum\Laravel\Enum;

/**
 * @method static self anime()
 * @method static self manga()
 * @OA\Schema(
 *   schema="top_reviews_type_enum",
 *   description="The type of reviews to filter by. Defaults to anime.",
 *   type="string",
 *   enum={"anime","manga"}
 * )
 */
final class TopReviewsTypeEnum extends Enum
{
}
