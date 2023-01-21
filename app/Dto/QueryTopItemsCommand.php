<?php

namespace App\Dto;

use Illuminate\Support\Optional;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Numeric;
use Spatie\LaravelData\Data;

abstract class QueryTopItemsCommand extends Data
{
    #[Numeric, Min(1)]
    public int|Optional $page;

    #[Numeric, Min(1)]
    public int|Optional $limit;
}
