<?php

namespace App;

use App\Concerns\FilteredByLetter;
use App\Enums\ClubCategoryEnum;
use App\Enums\ClubTypeEnum;
use Database\Factories\ClubFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jikan\Request\Club\ClubRequest;
use Laravel\Scout\Builder;
use MongoDB\BSON\UTCDateTime;

/**
 * Mongo-backed club document. Attributes are magic (from the collection); the
 * documented ones are those the app/resources actually read.
 *
 * @property int $mal_id
 * @property string $url
 * @property array<string, mixed> $images
 * @property string $name
 * @property int $members
 * @property string $category
 * @property string $type
 * @property string $access
 * @property UTCDateTime|null $created
 * @property UTCDateTime|null $createdAt
 * @property UTCDateTime|null $modifiedAt
 *
 * @use HasFactory<ClubFactory>
 */
class Club extends JikanApiSearchableModel
{
    use FilteredByLetter, HasFactory;

    protected array $filters = ['order_by', 'sort', 'letter', 'category', 'type'];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'mal_id', 'url', 'images', 'name', 'members', 'category', 'created', 'access', 'anime', 'manga', 'created_at', 'updated_at', 'characters', 'staff',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var list<string>
     */
    protected $appends = ['images'];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'clubs';

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var list<string>
     */
    protected $hidden = [
        '_id', 'request_hash', 'expiresAt',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->displayNameFieldName = 'name';
    }

    /** @noinspection PhpUnused */
    /**
     * @param  Builder<static>|\Illuminate\Database\Eloquent\Builder<static>  $query
     * @return Builder<static>|\Illuminate\Database\Eloquent\Builder<static>
     */
    public function filterByCategory(Builder|\Illuminate\Database\Eloquent\Builder $query, ClubCategoryEnum $value): Builder|\Illuminate\Database\Eloquent\Builder
    {
        return $query->where('category', $value->label);
    }

    /** @noinspection PhpUnused */
    /**
     * @param  Builder<static>|\Illuminate\Database\Eloquent\Builder<static>  $query
     * @return Builder<static>|\Illuminate\Database\Eloquent\Builder<static>
     */
    public function filterByType(Builder|\Illuminate\Database\Eloquent\Builder $query, ClubTypeEnum $value): Builder|\Illuminate\Database\Eloquent\Builder
    {
        return $query->where('access', $value->label);
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
        $data = app('JikanParser')->getClub(new ClubRequest($id));

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
            'category' => $this->category,
            'created' => $this->convertToTimestamp($this->created),
            'access' => $this->type,
            'members' => $this->members,
        ];
    }

    public function typesenseQueryBy(): array
    {
        return ['name'];
    }

    public function getTitleAttributeName(): string
    {
        return 'name';
    }
}
