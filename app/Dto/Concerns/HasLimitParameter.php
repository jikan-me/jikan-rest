<?php

namespace App\Dto\Concerns;

use App\Rules\Attributes\MaxLimitWithFallback;
use OpenApi\Attributes as OA;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Min;

#[OA\Parameter(
    name: 'limit',
    in: 'query',
    schema: new OA\Schema(type: 'integer'),
)]
trait HasLimitParameter
{
    use PreparesData;

    #[IntegerType, Min(1), MaxLimitWithFallback]
    public ?int $limit = null;
}
