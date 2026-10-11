<?php

namespace App;

use App\Concerns\FilteredByLetter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jikan\Jikan;
use Jikan\Request\Magazine\MagazinesRequest;

/**
 * Mongo-backed magazine document. Attributes are magic (from the collection); the
 * documented ones are those the app/resources actually read.
 *
 * @property int $mal_id
 * @property string $name
 * @property string $url
 * @property int $count
 * @use \Illuminate\Database\Eloquent\Factories\HasFactory<\Database\Factories\MagazineFactory>
 */
class Magazine extends JikanApiSearchableModel
{
    use FilteredByLetter, HasFactory;
    protected array $filters = ["order_by", "sort", "letter"];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'mal_id', 'name', 'url', 'count',
        'createdAt', 'modifiedAt'
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
     * @var list<string>
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
     * @return array<string, mixed>
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

    /**
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => (string) $this->mal_id,
            'mal_id' => (int) $this->mal_id,
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

    public function getTitleAttributeName(): string
    {
        return 'name';
    }
}
