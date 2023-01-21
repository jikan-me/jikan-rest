<?php

namespace App;

use App\Concerns\FilteredByLetter;
use Jikan\Jikan;
use Jikan\Request\Magazine\MagazinesRequest;

/**
 * Class Magazine
 * @package App
 */
class Magazine extends JikanApiSearchableModel
{
    use FilteredByLetter;
    protected array $filters = ["order_by", "sort", "letter"];

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

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->displayNameFieldName = "name";
    }

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
