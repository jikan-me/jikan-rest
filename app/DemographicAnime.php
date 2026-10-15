<?php

namespace App;

use App\Filters\FilterQueryString;

/**
 * Class DemographicAnime
 */
class DemographicAnime extends JikanApiModel
{
    use FilterQueryString;

    protected array $filters = [];

    protected $fillable = [
        'mal_id', 'name', 'url', 'count',
        'createdAt', 'modifiedAt',
    ];

    protected $table = 'demographics_anime';

    protected $hidden = [
        '_id', 'expiresAt',
    ];
}
