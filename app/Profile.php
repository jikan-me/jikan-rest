<?php

namespace App;

use App\Concerns\FilteredByLetter;
use Database\Factories\ProfileFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jikan\Request\User\UserProfileRequest;
use MongoDB\BSON\UTCDateTime;

/**
 * Mongo-backed user profile document. Attributes are magic (from the collection); the
 * documented ones are those the app/resources actually read.
 *
 * @property int $mal_id
 * @property string $username
 * @property string $url
 * @property array<string, mixed> $images
 * @property UTCDateTime|null $last_online
 * @property string|null $gender
 * @property UTCDateTime|null $birthday
 * @property string|null $location
 * @property UTCDateTime|null $joined
 * @property array<string, mixed> $anime_stats
 * @property array<string, mixed> $manga_stats
 * @property array<string, mixed> $favorites
 * @property array<string, mixed>|null $last_updates
 * @property string|null $about
 * @property array<string, mixed>|null $external_links
 * @property UTCDateTime|null $createdAt
 * @property UTCDateTime|null $modifiedAt
 *
 * @use HasFactory<ProfileFactory>
 */
class Profile extends JikanApiSearchableModel
{
    use FilteredByLetter, HasFactory;

    protected array $filters = [];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'mal_id', 'username', 'url', 'images', 'last_online', 'gender', 'birthday', 'location',
        'joined', 'anime_stats', 'manga_stats', 'favorites', 'about', 'last_updates', 'external_links',
        'createdAt', 'modifiedAt', 'internal_username',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var list<string>
     */
    protected $hidden = [
        '_id',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->displayNameFieldName = 'username';
    }

    /**
     * @return array<string, mixed>
     */
    public static function scrape(string $username): array
    {
        $data = app('JikanParser')->getUserProfile(new UserProfileRequest($username));

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
            'username' => $this->username,
        ];
    }

    public function typesenseQueryBy(): array
    {
        return [
            'username',
        ];
    }

    public function getTitleAttributeName(): string
    {
        return 'username';
    }
}
