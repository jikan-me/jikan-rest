<?php

namespace App\Rules\Attributes;

use Attribute;
use Spatie\Enum\Laravel\Rules\EnumRule;
use Spatie\LaravelData\Attributes\Validation\Rule;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class EnumValidation extends Rule
{
    public function __construct(string $enumClass)
    {
        parent::__construct(new EnumRule($enumClass));
    }
}
