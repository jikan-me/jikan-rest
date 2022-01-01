<?php

namespace App;

use App\Http\HttpHelper;
use Jenssegers\Mongodb\Eloquent\Model;
use Jikan\Helper\Media;
use Jikan\Helper\Parser;
use Jikan\Jikan;
use Jikan\Model\Common\YoutubeMeta;
use Jikan\Request\Anime\AnimeRequest;
use Jikan\Request\Manga\MangaRequest;

class Manga extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'mal_id', 'url', 'title', 'title_english', 'title_japanese', 'title_synonyms', 'images', 'status', 'type', 'volumes', 'chapters', 'publishing', 'published', 'rank', 'score',
        'scored_by', 'popularity', 'members', 'favorites', 'synopsis', 'background', 'related', 'genres', 'explicit_genres', 'themes', 'demographics', 'authors', 'serializations',
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
    protected $table = 'manga';

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        '_id', 'expiresAt', 'request_hash'
    ];

    public static function scrape(int $id)
    {
        $data = app('JikanParser')->getManga(new MangaRequest($id));

        return HttpHelper::serializeEmptyObjectsControllerLevel(
            json_decode(
                app('SerializerV4')
                ->serialize($data, 'json'),
                true
            )
        );
    }
}