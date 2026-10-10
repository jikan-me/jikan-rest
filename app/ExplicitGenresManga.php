<?php

namespace App;

use App\Filters\FilterQueryString;

/**
 * Class ExplicitGenresManga
 * @package App
 */
class ExplicitGenresManga extends JikanApiModel
{
    use FilterQueryString;

    protected array $filters = [];

    protected $fillable = [
        'mal_id', 'name', 'url', 'count',
        'createdAt', 'modifiedAt'
    ];

    protected $table = 'explicit_genres_manga';

    protected $hidden = [
        '_id', 'expiresAt'
    ];
}