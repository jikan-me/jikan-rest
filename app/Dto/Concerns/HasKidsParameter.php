<?php

namespace App\Dto\Concerns;

use App\Casts\ContextualBooleanCast;
use OpenApi\Annotations as OA;
use Spatie\LaravelData\Attributes\Validation\BooleanType;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Optional;

/**
 *  @OA\Parameter(
 *      name="kids",
 *      in="query",
 *      required=false,
 *      description="This is a flag. When supplied it will include entries with the Kids genres in specific endpoints that filter them out by default. You do not need to pass a value to it. e.g usage: `?kids`",
 *      @OA\Schema(type="boolean")
 * ),
 */
trait HasKidsParameter
{
    use PreparesData;

    #[BooleanType, WithCast(ContextualBooleanCast::class)]
    public bool|Optional $kids = false;
}
