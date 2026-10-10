<?php

namespace App;

use App\Filters\FilterQueryString;

/**
 * Class ThemeManga
 * @package App
 */
class ThemeManga extends JikanApiModel
{
    use FilterQueryString;

    protected array $filters = [];

    protected $fillable = [
        'mal_id', 'name', 'url', 'count',
        'createdAt', 'modifiedAt'
    ];

    protected $table = 'themes_manga';

    protected $hidden = [
        '_id', 'expiresAt'
    ];
}