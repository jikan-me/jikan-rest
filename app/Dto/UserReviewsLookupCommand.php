<?php

namespace App\Dto;

use App\Dto\Concerns\HasPageParameter;
use Illuminate\Http\JsonResponse;

/**
 * @extends LookupByUsernameCommand<JsonResponse>
 */
final class UserReviewsLookupCommand extends LookupByUsernameCommand
{
    use HasPageParameter;
}
