<?php

namespace App\Enums;

use Jikan\Helper\Constants as JikanConstants;
use Spatie\Enum\Laravel\Enum;

/**
 * @method static self any()
 * @method static self male()
 * @method static self female()
 * @method static self nonbinary()
 * @OA\Schema(
 *   schema="users_search_query_gender",
 *   description="Users Search Query Gender.",
 *   type="string",
 *   enum={"any","male","female","nonbinary"}
 * )
 */
final class GenderEnum extends Enum
{
    protected static function labels(): array
    {
        return [
            'any' => JikanConstants::SEARCH_USER_GENDER_ANY,
            'male' => JikanConstants::SEARCH_USER_GENDER_MALE,
            'female' => JikanConstants::SEARCH_USER_GENDER_FEMALE,
            'nonbinary' => JikanConstants::SEARCH_USER_GENDER_NONBINARY
        ];
    }
}
