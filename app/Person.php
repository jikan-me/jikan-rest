<?php

namespace App;

use App\Http\HttpHelper;
use Jenssegers\Mongodb\Eloquent\Model;
use Jikan\Helper\Media;
use Jikan\Helper\Parser;
use Jikan\Jikan;
use Jikan\Model\Common\YoutubeMeta;
use Jikan\Request\Anime\AnimeRequest;
use Jikan\Request\Character\CharacterRequest;
use Jikan\Request\Person\PersonRequest;

class Person extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'mal_id', 'url', 'images', 'website_url', 'name', 'given_name', 'family_name', 'alternative_names', 'birthday', 'member_favorites', 'about', 'voice_acting_roles', 'anime_staff_positions', 'published_manga'
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['images', 'favorites'];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'people';

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        '_id', 'images', 'member_favorites'
    ];

    public function getFavoritesAttribute()
    {
        return $this->attributes['member_favorites'];
    }


    public static function scrape(int $id)
    {
        $data = app('JikanParser')->getPerson(new PersonRequest($id));
        return json_decode(
            app('SerializerV4')
                ->serialize($data, 'json'),
            true
        );
    }
}