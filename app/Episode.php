<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jenssegers\Mongodb\Eloquent\Model;
use Jikan\Jikan;

class Episode extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'mal_id', 'title', 'title_japanese', 'title_romanji', 'aired', 'filler', 'recap', 'video_url', 'forum_url', 'synopsis'
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'anime_episode';

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        '_id', 'request_hash'
    ];

}
