<?php

namespace App\Enums;


use Spatie\Enum\Laravel\Enum;

/**
 * @method static self summer()
 * @method static self spring()
 * @method static self winter()
 * @method static self fall()
 */
final class AnimeSeasonEnum extends Enum
{
    protected static function labels(): array
    {
        $labels = [];
        foreach (self::values() as $value) {
            $labels[$value] = ucfirst($value);
        }

        return $labels;
    }
}
