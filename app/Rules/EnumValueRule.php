<?php

namespace App\Rules;

use Spatie\Enum\Laravel\Rules\EnumRule;

/**
 * spatie's EnumRule, but the failure message lists the accepted values instead of
 * exposing the PHP enum class name ("... is not a valid App\Enums\AnimeOrderByEnum").
 */
final class EnumValueRule extends EnumRule
{
    public function message(): string
    {
        return 'The :attribute must be one of: '.implode(', ', $this->getDisplayableOtherValues()).'.';
    }
}
