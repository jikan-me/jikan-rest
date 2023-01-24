<?php

namespace App\Dto\Concerns;

use App\Rules\Attributes\MaxLimitWithFallback;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Optional;

trait HasLimitParameter
{
    use MapsDefaultLimitParameter;

    #[IntegerType, Min(1), MaxLimitWithFallback]
    public int|Optional $limit;
}
