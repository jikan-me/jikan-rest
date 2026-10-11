<?php

namespace App;

use App\Concerns\FilteredByLetter;
use Jikan\Jikan;
use Jikan\Request\Person\PersonRequest;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Mongo-backed person document. Attributes are magic (from the collection); the
 * documented ones are those the app/resources actually read.
 *
 * @property int $mal_id
 * @property string $url
 * @property array<string, mixed> $images
 * @property string|null $website_url
 * @property string $name
 * @property string|null $given_name
 * @property string|null $family_name
 * @property array<string, mixed> $alternate_names
 * @property \MongoDB\BSON\UTCDateTime|null $birthday
 * @property int $favorites
 * @property int $member_favorites
 * @property string|null $about
 * @property array<string, mixed> $voice_acting_roles
 * @property array<string, mixed> $anime_staff_positions
 * @property array<string, mixed> $published_manga
 * @property \MongoDB\BSON\UTCDateTime|null $createdAt
 * @property \MongoDB\BSON\UTCDateTime|null $modifiedAt
 * @use \Illuminate\Database\Eloquent\Factories\HasFactory<\Database\Factories\PersonFactory>
 */
class Person extends JikanApiSearchableModel
{
    use HasFactory, FilteredByLetter;
    protected array $filters = ["order_by", "sort"];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'mal_id', 'url', 'images', 'website_url', 'name', 'given_name', 'family_name',
        'alternative_names', 'birthday', 'member_favorites', 'about', 'voice_acting_roles',
        'anime_staff_positions', 'published_manga',
        'createdAt', 'modifiedAt'
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
    protected $table = 'people';

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var list<string>
     */
    protected $hidden = [
        '_id', 'member_favorites'
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->displayNameFieldName = "name";
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
        $data = app('JikanParser')->getPerson(new PersonRequest($id));
        return json_decode(
            app('SerializerV4')
                ->serialize($data, 'json'),
            true
        );
    }

    /**
     * Converts the model to an index-able data array.
     *
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => (string) $this->mal_id,
            'mal_id' => (int) $this->mal_id,
            'name' => $this->name,
            'given_name' => $this->given_name,
            'family_name' => $this->family_name,
            'alternate_names' => $this->alternate_names,
            'member_favorites' => $this->member_favorites,
            'birthday' => $this->convertToTimestamp($this->birthday)
        ];
    }

    public function typesenseQueryBy(): array
    {
        return [
            "name",
            "given_name",
            "family_name",
            "alternate_names"
        ];
    }

    public function getTitleAttributeName(): string
    {
        return 'name';
    }
}
