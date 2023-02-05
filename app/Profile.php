<?php

namespace App;

use App\Concerns\FilteredByLetter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jikan\Request\User\UserProfileRequest;

class Profile extends JikanApiSearchableModel
{
    use FilteredByLetter, HasFactory;
    protected array $filters = [];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'mal_id', 'username', 'url', 'images', 'last_online', 'gender', 'birthday', 'location', 'joined', 'anime_stats', 'manga_stats', 'favorites', 'about'
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
     * @var array
     */
    protected $hidden = [
        '_id',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->displayNameFieldName = "username";
    }

    public static function scrape(string $username)
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
     * @return array
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => (string) $this->mal_id,
            'mal_id' => (int) $this->mal_id,
            'username' => $this->username
        ];
    }

    public function typesenseQueryBy(): array
    {
        return [
            "username"
        ];
    }
}
