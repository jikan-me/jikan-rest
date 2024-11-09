<?php

namespace App\Dto\Concerns;

use App\Rules\Attributes\MaxLimitWithFallback;
use OpenApi\Annotations as OA;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Optional;


/**
 *  @OA\Parameter(
 *    name="limit",
 *    in="query",
 *    @OA\Schema(type="integer")
 *  ),
 */
trait HasLimitParameterWithSmallerMax
{
    use PreparesData;

    #[IntegerType, Min(1), MaxLimitWithFallback(5)]
    public int|Optional $limit;
}
