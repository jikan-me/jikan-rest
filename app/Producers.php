<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;
use Jikan\Request\Producer\ProducerRequest;

class Producers extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'mal_id', 'url', 'images', 'titles', 'established', 'favorites', 'about', 'external', 'count'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'producers';

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        '_id', 'request_hash', 'expiresAt'
    ];

    public static function scrape(int $id)
    {
        $data = app('JikanParser')->getProducer(new ProducerRequest($id));

        $data = json_decode(
            app('SerializerV4')
                ->serialize($data, 'json'),
            true
        );
        unset($data['results'], $data['has_next_page'], $data['last_visible_page']);

        return $data;
    }
}
