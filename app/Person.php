<?php

namespace App;

use App\Concerns\FilteredByLetter;
use Jikan\Jikan;
use Jikan\Request\Person\PersonRequest;
use function Symfony\Component\Translation\t;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Person extends JikanApiSearchableModel
{
    use HasFactory, FilteredByLetter;
    protected array $filters = ["order_by", "sort"];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'mal_id', 'url', 'images', 'website_url', 'name', 'given_name', 'family_name', 'alternative_names', 'birthday', 'member_favorites', 'about', 'voice_acting_roles', 'anime_staff_positions', 'published_manga'
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
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
     * @var array
     */
    protected $hidden = [
        '_id', 'images', 'member_favorites'
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->displayNameFieldName = "name";
    }

    public function getFavoritesAttribute()
    {
        return $this->attributes['member_favorites'];
    }


    public static function scrape(int $id)
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
     * @return array
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => (string) $this->mal_id,
            'mal_id' => (string) $this->mal_id,
            'name' => $this->name,
            'given_name' => $this->given_name,
            'family_name' => $this->family_name,
            'alternate_names' => $this->alternate_names
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
}
