<?php

namespace App\Rules\Attributes;

use App\Rules\EnumValueRule;
use Attribute;
use Spatie\LaravelData\Attributes\Validation\Rule;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class EnumValidation extends Rule
{
    public function __construct(string $enumClass)
    {
        parent::__construct(new EnumValueRule($enumClass));
    }
}
