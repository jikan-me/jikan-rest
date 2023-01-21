<?php

namespace App\Enums;


use Spatie\Enum\Laravel\Enum;

/**
 * @method static self monday()
 * @method static self tuesday()
 * @method static self wednesday()
 * @method static self thursday()
 * @method static self friday()
 * @method static self saturday()
 * @method static self sunday()
 * @method static self other()
 * @method static self unknown()
 */
final class AnimeScheduleFilterEnum extends Enum
{
    public function isWeekDay(): bool
    {
        return $this->value !== self::other()->value && $this->value !== self::unknown()->value;
    }

    protected static function labels(): array
    {
        return [
            ...collect(self::values())->map(fn ($x) => ucfirst($x))->toArray(),
            "other" => "Not scheduled once per week",
            "unknown" => "Unknown"
        ];
    }
}
