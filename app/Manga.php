<?php

namespace App;

use App\Http\HttpHelper;
use Database\Factories\MangaFactory;
use Illuminate\Support\Facades\App;
use Jikan\Jikan;
use Jikan\Request\Manga\MangaRequest;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Manga extends JikanApiSearchableModel
{
    use HasFactory;

    // note that here we skip "score", "min_score", "max_score", "rating" and others because they need special logic
    // to set the correct filtering on the ORM.
    protected array $filters = ["order_by", "status", "type", "sort"];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'mal_id', 'url', 'title', 'title_english', 'title_japanese', 'title_synonyms', 'titles', 'images', 'status', 'type', 'volumes', 'chapters', 'publishing', 'published', 'rank', 'score',
        'scored_by', 'popularity', 'members', 'favorites', 'synopsis', 'background', 'related', 'genres', 'explicit_genres', 'themes', 'demographics', 'authors', 'serializations',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [];


    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'manga';

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        '_id', 'expiresAt', 'request_hash'
    ];

    public static function scrape(int $id)
    {
        $data = app('JikanParser')->getManga(new MangaRequest($id));

        return HttpHelper::serializeEmptyObjectsControllerLevel(
            json_decode(
                app('SerializerV4')
                ->serialize($data, 'json'),
                true
            )
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
            'start_date' => $this->convertToTimestamp($this->published['from']),
            'end_date' => $this->convertToTimestamp($this->published['to']),
            'title' => $this->title,
            'title_transformed' => $this->simplifyStringForSearch($this->title),
            'title_english' => $this->title_english ?? "",
            'title_english_transformed' => $this->simplifyStringForSearch($this->title_english),
            'title_japanese' => $this->title_japanese,
            'title_japanese_transformed' => $this->simplifyStringForSearch($this->title_japanese),
            'title_synonyms' => $this->title_synonyms,
            'type' => $this->type,
            'chapters' => $this->chapters,
            'volumes' => $this->volumes,
            'status' => $this->status,
            'publishing' => $this->publishing,
            'score' => $this->score,
            'rank' => $this->rank,
            'popularity' => $this->popularity,
            'members' => $this->members,
            'favorites' => $this->favorites,
            'synopsis' => $this->synopsis,
            'season' => $this->season,
            'magazines' => $this->getMalIdsOfField($this->magazines),
            'genres' => $this->getMalIdsOfField($this->genres),
            'explicit_genres' => $this->getMalIdsOfField($this->explicit_genres)
        ];
    }

    public function getThemesAttribute(): array
    {
        return [];
    }

    public function typesenseQueryBy(): array
    {
        return [
            'title',
            'title_transformed',
            'title_english',
            'title_english_transformed',
            'title_japanese',
            'title_japanese_transformed',
        ];
    }

    public function getTypeSenseQueryByWeights(): string|null
    {
        return "2,2,1,1,2,2";
    }

    /**
     * Returns which fields the search index should sort on when searching
     * @return array|null
     */
    public function getSearchIndexSortBy(): array|null
    {
        return [
            [
                "field" => "_text_match",
                "direction" => "desc"
            ],
            [
                "field" => "members",
                "direction" => "desc"
            ],
        ];
    }

    protected static function newFactory()
    {
        return App::make(MangaFactory::class);
    }
}
