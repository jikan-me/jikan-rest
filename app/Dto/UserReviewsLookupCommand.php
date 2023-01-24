<?php

namespace App\Dto;


use App\Dto\Concerns\HasPageParameter;
use Illuminate\Http\JsonResponse;

/**
 * @extends LookupDataCommand<JsonResponse>
 */
final class UserReviewsLookupCommand extends LookupByUsernameCommand
{
    use HasPageParameter;
}
