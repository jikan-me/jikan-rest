<?php

namespace App;

use App\Concerns\FilteredByLetter;
use Database\Factories\CharacterFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jikan\Request\Character\CharacterRequest;
use MongoDB\BSON\UTCDateTime;

/**
 * Mongo-backed character document. Attributes are magic (from the collection); the
 * documented ones are those the app/resources actually read.
 *
 * @property int $mal_id
 * @property string $url
 * @property array<string, mixed> $images
 * @property string $name
 * @property string|null $name_kanji
 * @property array<string, mixed> $nicknames
 * @property int $favorites
 * @property int $member_favorites
 * @property string|null $about
 * @property array<string, mixed> $animeography
 * @property array<string, mixed> $mangaography
 * @property array<string, mixed> $voice_actors
 * @property UTCDateTime|null $createdAt
 * @property UTCDateTime|null $modifiedAt
 *
 * @use HasFactory<CharacterFactory>
 */
class Character extends JikanApiSearchableModel
{
    use FilteredByLetter, HasFactory;

    protected array $filters = ['order_by', 'sort', 'letter'];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'mal_id', 'url', 'name', 'name_kanji', 'nicknames', 'about', 'member_favorites',
        'images', 'animeography', 'mangaography', 'voice_actors',
        'createdAt', 'modifiedAt',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var list<string>
     */
    protected $appends = ['images', 'favorites'];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'characters';

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var list<string>
     */
    protected $hidden = [
        '_id', 'trailer_url', 'premiered', 'opening_themes', 'ending_themes', 'member_favorites',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->displayNameFieldName = 'name';
    }

    /** @noinspection PhpUnused */
    public function getFavoritesAttribute(): mixed
    {
        return $this->attributes['member_favorites'];
    }

    /** @noinspection PhpUnused */
    /**
     * @return array<string, mixed>
     */
    public function getImagesAttribute(): array
    {
        return $this->attributes['images'];
    }

    /**
     * @return array<string, mixed>
     */
    public static function scrape(int $id): array
    {
        $data = app('JikanParser')->getCharacter(new CharacterRequest($id));

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
            'name_kanji' => $this->name_kanji,
            'member_favorites' => $this->member_favorites,
        ];
    }

    public function typesenseQueryBy(): array
    {
        return [
            'name',
            'name_kanji',
        ];
    }

    public function getTitleAttributeName(): string
    {
        return 'name';
    }
}
