<?php

namespace App;

use App\Filters\FilterQueryString;

/**
 * Class ExplicitGenresAnime
 * @package App
 */
class ExplicitGenresAnime extends JikanApiModel
{
    use FilterQueryString;

    protected array $filters = [];

    protected $fillable = [
        'mal_id', 'name', 'url', 'count',
        'createdAt', 'modifiedAt'
    ];

    protected $table = 'explicit_genres_anime';

    protected $hidden = [
        '_id', 'expiresAt'
    ];
}