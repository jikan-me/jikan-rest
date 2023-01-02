<?php

namespace App\Dto;

use Illuminate\Support\Optional;
use Spatie\LaravelData\Data;

abstract class QueryTopItemsCommand extends Data
{
    public int|Optional $page;

    public int|Optional $limit;
}
