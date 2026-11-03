<?php

namespace App\Dto;

use Illuminate\Http\JsonResponse;

/**
 * @extends LookupByUsernameCommand<JsonResponse>
 */
final class UserUpdatesLookupCommand extends LookupByUsernameCommand {}
