<?php

namespace App;

use App\Concerns\FilteredByLetter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jikan\Request\Producer\ProducerRequest;

/**
 * Mongo-backed producer document. Attributes are magic (from the collection); the
 * documented ones are those the app/resources actually read.
 *
 * @property int $mal_id
 * @property string $url
 * @property array<string, mixed> $images
 * @property array<string, mixed>|null $titles
 * @property \MongoDB\BSON\UTCDateTime|null $established
 * @property int $favorites
 * @property string|null $about
 * @property int $count
 * @property array<string, mixed>|null $external_links
 * @property \MongoDB\BSON\UTCDateTime|null $createdAt
 * @property \MongoDB\BSON\UTCDateTime|null $modifiedAt
 * @use \Illuminate\Database\Eloquent\Factories\HasFactory<\Database\Factories\ProducersFactory>
 */
class Producers extends JikanApiSearchableModel
{
    use FilteredByLetter, HasFactory;
    protected array $filters = ["order_by", "sort", "letter"];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
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
     * @var list<string>
     */
    protected $hidden = [
        '_id', 'request_hash', 'expiresAt'
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->displayNameFieldName = "titles.0.title";
    }

    /**
     * @return array<string, mixed>
     */
    public static function scrape(int $id): array
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

    /**
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        $titles = !is_null($this->titles) ? collect($this->titles)->map(fn ($x) => $x["title"])->toArray() : [''];

        return [
            'id' => (string) $this->mal_id,
            'mal_id' => (int) $this->mal_id,
            'url' => !is_null($this->url) ? $this->url : '',
            'titles' => implode(', ', $titles),
            'established' => $this->convertToTimestamp($this->established),
            'favorites' => $this->favorites,
            'count' => $this->count
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getCollectionSchema(): array
    {
        return [
            'name' => $this->searchableAs(),
            'fields' => [
                [
                    'name' => '.*',
                    'type' => 'auto',
                ],
                [
                    'name' => 'titles',
                    'type' => 'string',
                    'optional' => false,
                    'infix' => true,
                    'sort' => true
                ],
                [
                    'name' => 'url',
                    'type' => 'string',
                    'optional' => false,
                    'infix' => true,
                    'sort' => true
                ],
            ]
        ];
    }

    public function typesenseQueryBy(): array
    {
        return [
            'url',
            'titles'
        ];
    }

    public function getTitleAttributeName(): string
    {
        return 'titles';
    }
}
