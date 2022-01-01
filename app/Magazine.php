<?php

namespace App;

use App\Http\HttpHelper;
use Jenssegers\Mongodb\Eloquent\Model;
use Jikan\Helper\Media;
use Jikan\Helper\Parser;
use Jikan\Jikan;
use Jikan\Model\Common\YoutubeMeta;
use Jikan\Request\Magazine\MagazinesRequest;

/**
 * Class Magazine
 * @package App
 */
class Magazine extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'mal_id', 'name', 'url', 'count'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'magazines';


    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        '_id', 'expiresAt'
    ];

    /**
     * @return array
     */
    public static function scrape() : array
    {
        $data = app('JikanParser')->getMagazines(new MagazinesRequest());

        return json_decode(
            app('SerializerV4')
                ->serialize($data, 'json'),
            true
        );
    }
}