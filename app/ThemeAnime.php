<?php

namespace App;

use App\Filters\FilterQueryString;

/**
 * Class ThemeAnime
 * @package App
 */
class ThemeAnime extends JikanApiModel
{
    use FilterQueryString;

    protected array $filters = [];

    protected $fillable = [
        'mal_id', 'name', 'url', 'count',
        'createdAt', 'modifiedAt'
    ];

    protected $table = 'themes_anime';

    protected $hidden = [
        '_id', 'expiresAt'
    ];
}