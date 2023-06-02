<?php

namespace App\Dto\Concerns;

use OpenApi\Annotations as OA;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Numeric;
use Spatie\LaravelData\Optional;

/**
 *  @OA\Parameter(
 *    name="page",
 *    in="query",
 *    @OA\Schema(type="integer")
 *  ),
 */
trait HasPageParameter
{
    #[Numeric, Min(1)]
    public int|Optional $page = 1;
}
