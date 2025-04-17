<?php

namespace App\Rules\Attributes;

use App\Rules\MaxResultsPerPageRule;
use Attribute;
use Spatie\LaravelData\Attributes\Validation\Rule;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class MaxLimitWithFallback extends Rule
{
    public function __construct(?int $fallbackLimit = null)
    {
        parent::__construct(new MaxResultsPerPageRule($fallbackLimit ?? max_results_per_page()));
    }
}
