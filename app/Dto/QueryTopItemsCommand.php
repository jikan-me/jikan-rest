<?php

namespace App\Dto;

use App\Dto\Concerns\HasLimitParameter;
use App\Dto\Concerns\HasPageParameter;
use Spatie\LaravelData\Data;

abstract class QueryTopItemsCommand extends Data
{
    use HasLimitParameter, HasPageParameter;
}
