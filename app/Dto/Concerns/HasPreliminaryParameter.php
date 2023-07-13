<?php

namespace App\Dto\Concerns;

use App\Casts\ContextualBooleanCast;
use OpenApi\Annotations as OA;
use Spatie\LaravelData\Attributes\Validation\BooleanType;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Optional;

/**
 *  @OA\Parameter(
 *      name="preliminary",
 *      in="query",
 *      required=false,
 *      description="Any reviews left during an ongoing anime/manga, those reviews are tagged as preliminary. Preliminary reviews are not returned by default. e.g usage: `?preliminary=true`",
 *      @OA\Schema(type="boolean")
 * ),
 */
trait HasPreliminaryParameter
{
    use PreparesData;

    #[BooleanType, WithCast(ContextualBooleanCast::class)]
    public bool|Optional $preliminary;
}
