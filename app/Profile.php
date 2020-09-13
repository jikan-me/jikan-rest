<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;
use Jikan\Request\User\UserProfileRequest;

class Profile extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'mal_id', 'username', 'url', 'images', 'last_online', 'gender', 'birthday', 'location', 'joined', 'anime_stats', 'manga_stats', 'favorites', 'about'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        '_id',
    ];

    public static function scrape(string $username)
    {
        $data = app('JikanParser')->getUserProfile(new UserProfileRequest($username));

        return json_decode(
            app('SerializerV4')
                ->serialize($data, 'json'),
            true
        );
    }
}