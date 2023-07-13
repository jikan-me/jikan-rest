<?php

namespace App\Dto\Concerns;

use App\Casts\ContextualBooleanCast;
use OpenApi\Annotations as OA;
use Spatie\LaravelData\Attributes\Validation\BooleanType;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Optional;

/**
 *  @OA\Parameter(
 *      name="spoiler",
 *      in="query",
 *      required=false,
 *      description="Any reviews that are tagged as a spoiler. Spoiler reviews are not returned by default. e.g usage: `?spoiler=true`",
 *      @OA\Schema(type="boolean")
 * ),
 */
trait HasSpoilersParameter
{
    use PreparesData;

    #[BooleanType, WithCast(ContextualBooleanCast::class)]
    public bool|Optional $spoilers;
}
