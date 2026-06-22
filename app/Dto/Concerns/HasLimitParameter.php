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
 *    required=false,
 *    description="Maximum limit (and the default number of entries returned) is 25 for all endpoints except for Random endpoints where the maximum limit is 5 and the default number of entries returned is 1.",
 *    @OA\Schema(type="integer")
 *  ),
 */
trait HasLimitParameter
{
    use PreparesData;

    #[IntegerType, Min(1), MaxLimitWithFallback]
    public int|Optional $limit;
}
