<?php

namespace App\Dto\Concerns;

use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Numeric;
use Spatie\LaravelData\Optional;

trait HasPageParameter
{
    #[Numeric, Min(1)]
    public int|Optional $page = 1;
}
