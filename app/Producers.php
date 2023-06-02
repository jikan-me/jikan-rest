<?php

namespace App;

use App\Concerns\FilteredByLetter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jikan\Request\Producer\ProducerRequest;

class Producers extends JikanApiSearchableModel
{
    use FilteredByLetter, HasFactory;
    protected array $filters = ["order_by", "sort", "letter"];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'mal_id', 'url', 'images', 'titles', 'established', 'favorites', 'about', 'external', 'count',
        'createdAt', 'modifiedAt'
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

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->displayNameFieldName = "titles.0.title";
    }

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

    public function toSearchableArray(): array
    {
        return [
            'id' => (string) $this->mal_id,
            'mal_id' => (int) $this->mal_id,
            'url' => !is_null($this->url) ? $this->url : '',
            'titles' => !is_null($this->titles) ? collect($this->titles)->map(fn ($x) => $x["title"])->toArray() : [''],
            'established' => $this->convertToTimestamp($this->established),
            'favorites' => $this->favorites,
            'count' => $this->count
        ];
    }

    public function typesenseQueryBy(): array
    {
        return [
            'url',
            'titles'
        ];
    }
}
