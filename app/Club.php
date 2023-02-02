<?php

namespace App;

use App\Concerns\FilteredByLetter;
use App\Enums\ClubCategoryEnum;
use App\Enums\ClubTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jikan\Request\Club\ClubRequest;

class Club extends JikanApiSearchableModel
{
    use FilteredByLetter, HasFactory;

    protected array $filters = ["order_by", "sort", "letter", "category", "type"];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'mal_id', 'url', 'images', 'name', 'members', 'category', 'created', 'access', 'anime', 'manga', 'created_at', 'updated_at', 'characters', 'staff'
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
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
     * @var array
     */
    protected $hidden = [
        '_id', 'request_hash', 'expiresAt', 'images'
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->displayNameFieldName = "name";
    }

    /** @noinspection PhpUnused */
    public function filterByCategory(\Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder $query, ClubCategoryEnum $value): \Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder
    {
        return $query->where("category", $value->label);
    }

    /** @noinspection PhpUnused */
    public function filterByType(\Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder $query, ClubTypeEnum $value): \Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder
    {
        return $query->where("access", $value->label);
    }

    public static function scrape(int $id)
    {
        $data = app('JikanParser')->getClub(new ClubRequest($id));

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
            'mal_id' => (int) $this->mal_id,
            'name' => $this->name,
            'category' => $this->category,
            'created' => $this->convertToTimestamp($this->created),
            'access' => $this->type,
            'members' => $this->members
        ];
    }

    public function typesenseQueryBy(): array
    {
        return ['name'];
    }
}
