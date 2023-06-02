<?php

namespace App\Dto;


use Illuminate\Http\JsonResponse;

/**
 * @extends LookupDataCommand<JsonResponse>
 */
final class UserFavoritesLookupCommand extends LookupByUsernameCommand
{
}
