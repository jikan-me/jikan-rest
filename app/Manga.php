<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class Manga extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'mal_id', 'url', 'title_english', 'title_synonyms', 'title_japanese', 'status', 'image_url', 'type', 'volumes', 'chapters', 'publishing', 'published', 'rank', 'score', 'scored_by', 'popularity', 'members', 'favorites', 'synopsis', 'background', 'related', 'genres', 'authors', 'serializations'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'manga';

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        '_id', 'expiresAt'
    ];
}