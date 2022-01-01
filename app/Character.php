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

class Character extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'mal_id', 'url', 'name', 'name_kanji', 'nicknames', 'about', 'member_favorites', 'images', 'animeography', 'mangaography', 'voice_actors'
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
    protected $table = 'characters';

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        '_id', 'trailer_url', 'premiered', 'opening_themes', 'ending_themes', 'images', 'member_favorites'
    ];

    public function getFavoritesAttribute()
    {
        return $this->attributes['member_favorites'];
    }

    public static function scrape(int $id)
    {
        $data = app('JikanParser')->getCharacter(new CharacterRequest($id));
        return json_decode(
            app('SerializerV4')
                ->serialize($data, 'json'),
            true
        );
    }
}