<?php

namespace App;

use App\Filters\FilterQueryString;

/**
 * Class DemographicManga
 */
class DemographicManga extends JikanApiModel
{
    use FilterQueryString;

    protected array $filters = [];

    protected $fillable = [
        'mal_id', 'name', 'url', 'count',
        'createdAt', 'modifiedAt',
    ];

    protected $table = 'demographics_manga';

    protected $hidden = [
        '_id', 'expiresAt',
    ];
}
