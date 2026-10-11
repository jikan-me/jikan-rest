<?php

namespace App;

use Jikan\Request\Genre\AnimeGenresRequest;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Mongo-backed manga genre document. Attributes are magic (from the collection); the
 * documented ones are those the app/resources actually read.
 *
 * @property int $mal_id
 * @property string $name
 * @property string $url
 * @property int $count
 * @use \Illuminate\Database\Eloquent\Factories\HasFactory<\Database\Factories\GenreMangaFactory>
 */
class GenreManga extends JikanApiSearchableModel
{
    use HasFactory;

    protected array $filters = [];

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
    protected $table = 'genres_manga';

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var list<string>
     */
    protected $hidden = [
        '_id', 'expiresAt'
    ];

    /**
     * @return array<string, mixed>
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
