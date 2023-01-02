<?php

namespace App\Enums;

use Jikan\Helper\Constants as JikanConstants;

/**
 * @method static self any()
 * @method static self male()
 * @method static self female()
 * @method static self nonbinary()
 */
final class GenderEnum extends \Spatie\Enum\Laravel\Enum
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
