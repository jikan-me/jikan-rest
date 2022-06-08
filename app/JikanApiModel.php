<?php

namespace App;

use App\Filters\FilterQueryString;

class JikanApiModel extends \Jenssegers\Mongodb\Eloquent\Model
{
    use FilterQueryString;

    /**
     * The list of parameters which can be used to filter the resultset from the database.
     * The available field names and "order_by" is allowed as values. If "order_by" is specified then
     * @var string[]
     */
    protected array $filters = [];
}
