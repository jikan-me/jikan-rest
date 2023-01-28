<?php

namespace App\Dto;

use App\Dto\Concerns\HasPageParameter;
use Illuminate\Http\JsonResponse;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Numeric;
use Spatie\LaravelData\Optional;

/**
 * @extends LookupDataCommand<JsonResponse>
 */
final class ClubMembersLookupCommand extends LookupDataCommand
{
    // use HasPageParameter;
    #[Numeric, Min(1)]
    public int|Optional $page = 1;
}
