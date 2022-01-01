<?php

namespace App;

use App\Http\HttpHelper;
use Jenssegers\Mongodb\Eloquent\Model;
use Jikan\Request\Anime\AnimeRequest;
use Jikan\Request\Club\ClubRequest;

class Club extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'mal_id', 'url', 'images', 'title', 'members_count', 'pictures_count', 'category', 'created', 'type', 'staff', 'anime_relations', 'manga_relations', 'character_relations'
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['images'];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'clubs';

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        '_id', 'request_hash', 'expiresAt', 'images'
    ];

    public static function scrape(int $id)
    {
        $data = app('JikanParser')->getClub(new ClubRequest($id));

        return json_decode(
            app('SerializerV4')
                ->serialize($data, 'json'),
            true
        );
    }
}