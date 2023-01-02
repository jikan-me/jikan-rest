<?php

namespace App;

use App\Concerns\FilteredByLetter;
use Jikan\Jikan;
use Jikan\Request\Character\CharacterRequest;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Character extends JikanApiSearchableModel
{
    use HasFactory, FilteredByLetter;

    protected array $filters = ["order_by", "sort", "letter"];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'mal_id', 'url', 'name', 'name_kanji', 'nicknames', 'about', 'member_favorites', 'images', 'animeography', 'mangaography', 'voice_actors'
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['images', 'favorites'];

    protected ?string $displayNameFieldName = "name";

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'characters';

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        '_id', 'trailer_url', 'premiered', 'opening_themes', 'ending_themes', 'images', 'member_favorites'
    ];

    /** @noinspection PhpUnused */
    public function getFavoritesAttribute()
    {
        return $this->attributes['member_favorites'];
    }

    public static function scrape(int $id)
    {
        $data = app('JikanParser')->getCharacter(new CharacterRequest($id));
        return json_decode(
            app('SerializerV4')
                ->serialize($data, 'json'),
            true
        );
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => (string) $this->mal_id,
            'mal_id' => (string) $this->mal_id,
            'name' => $this->name,
            'name_kanji' => $this->name_kanji,
            'member_favorites' => $this->member_favorites
        ];
    }

    public function typesenseQueryBy(): array
    {
        return [
            'name',
            'name_kanji'
        ];
    }
}
