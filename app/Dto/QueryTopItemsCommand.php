<?php

namespace App\Dto;

use App\Dto\Concerns\MapsDefaultLimitParameter;
use App\Rules\Attributes\MaxLimitWithFallback;
use Illuminate\Support\Optional;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Numeric;
use Spatie\LaravelData\Data;

abstract class QueryTopItemsCommand extends Data
{
    use MapsDefaultLimitParameter;

    #[Numeric, Min(1)]
    public int|Optional $page = 1;

    #[Numeric, Min(1), MaxLimitWithFallback]
    public int|Optional $limit;
}
