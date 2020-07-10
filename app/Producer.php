<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;
use Jikan\Request\Producer\ProducersRequest;

/**
 * Class Magazine
 * @package App
 */
class Producer extends Model
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
    protected $table = 'producers';


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
        $data = app('JikanParser')->getProducers(new ProducersRequest());

        return json_decode(
            app('SerializerV4')
                ->serialize($data, 'json'),
            true
        );
    }
}