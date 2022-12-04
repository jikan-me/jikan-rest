<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;
use Jikan\Request\Genre\AnimeGenresRequest;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class Magazine
 * @package App
 */
class GenreManga extends JikanApiSearchableModel
{
    use HasFactory;

    protected array $filters = ["order_by", "sort"];

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
    protected $table = 'genres_manga';

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
        $data = app('JikanParser')->getAnimeGenres(new AnimeGenresRequest());

        return json_decode(
            app('SerializerV4')
                ->serialize($data, 'json'),
            true
        );
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => (string) $this->mal_id,
            'mal_id' => (string) $this->mal_id,
            'name' => $this->name,
            'count' => $this->count
        ];
    }

    public function typesenseQueryBy(): array
    {
        return [
            'name'
        ];
    }
}
