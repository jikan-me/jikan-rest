<?php

namespace App;

use App\Concerns\FilteredByLetter;
use App\Concerns\MediaFilters;
use App\Enums\AnimeRatingEnum;
use App\Enums\AnimeTypeEnum;
use App\Http\HttpHelper;
use Carbon\CarbonImmutable;
use Database\Factories\AnimeFactory;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Jikan\Helper\Constants;
use Jikan\Jikan;
use Jikan\Request\Anime\AnimeRequest;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Model\BSONDocument;

class Anime extends JikanApiSearchableModel
{
    use HasFactory, MediaFilters, FilteredByLetter;

    protected array $filters = [
        "order_by", "status", "type", "sort", "max_score", "min_score", "score", "rating", "start_date", "end_date",
        "producer", "producers", "letter", "genres", "genres_exclude", "sfw", "unapproved", "kids"
    ];
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'mal_id', 'url', 'title', 'title_english', 'title_japanese', 'title_synonyms',
        'titles', 'images', 'type', 'source', 'episodes', 'status', 'airing', 'aired',
        'duration', 'rating', 'score', 'scored_by', 'rank', 'popularity', 'members',
        'favorites', 'synopsis', 'background', 'premiered', 'broadcast', 'related',
        'producers', 'licensors', 'studios', 'genres', 'explicit_genres', 'themes',
        'demographics', 'opening_themes', 'ending_themes', 'trailer', 'approved', 'createdAt', 'modifiedAt'
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['season', 'year', 'themes'];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'anime';

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        '_id', 'premiered', 'request_hash', 'expiresAt'
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->displayNameFieldName = "title";
    }

    public function setSeasonAttribute($value)
    {
        // noop
        // this attribute is calculated
    }

    public function getSeasonAttribute()
    {
        $premiered = array_key_exists('premiered', $this->attributes) ? $this->attributes['premiered'] : null;

        if (empty($premiered)
            || is_null($premiered)
            || !preg_match('~(Winter|Spring|Summer|Fall|)\s([\d+]{4})~', $premiered)
        ) {
            return null;
        }

        $season = explode(' ', $premiered)[0];
        return strtolower($season);
    }

    public function setYearAttribute($value)
    {
        // noop
        // this attribute is calculated
    }

    public function getYearAttribute()
    {
        $premiered = array_key_exists('premiered', $this->attributes) ? $this->attributes['premiered'] : null;

        if (empty($premiered)
            || is_null($premiered)
            || !is_string($premiered)
            || !preg_match('~(Winter|Spring|Summer|Fall|)\s([\d+]{4})~', $premiered)
        ) {
            return null;
        }

        return (int)explode(' ', $premiered)[1];
    }

    public function setBroadcastAttribute($value)
    {
        $this->attributes['broadcast'] = $this->adaptBroadcastValue($value);
    }

    public function getBroadcastAttribute()
    {
        if (array_key_exists("broadcast", $this->attributes)) {
            return $this->adaptBroadcastValue($this->attributes['broadcast']);
        }

        return [
            'day' => null,
            'time' => null,
            'timezone' => null,
            'string' => null
        ];
    }

    public function getThemesAttribute()
    {
        $result = [];
        if (array_key_exists("themes", $this->attributes)) {
            $result = $this->attributes["themes"];
        }

        return $result;
    }

    /** @noinspection PhpUnused */
    public function filterByType(\Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder $query, AnimeTypeEnum $value): \Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder
    {
        return $query->where("type", $value->label);
    }

    /** @noinspection PhpUnused */
    public function filterByRating(\Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder $query, AnimeRatingEnum $value): \Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder
    {
        return $query->where("rating", $value->label);
    }

    /** @noinspection PhpUnused */
    public function filterByStartDate(\Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder $query, CarbonImmutable $value): \Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder
    {
        return $query
            ->where("aired.from", ">=",
                $value
                    ->setTime(0, 0)
                    ->setTimezone(new \DateTimeZone('UTC'))
                    ->toAtomString()
            );
    }

    /** @noinspection PhpUnused */
    public function filterByEndDate(\Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder $query, CarbonImmutable $value): \Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder
    {
        return $query
            ->where("aired.to", "<=",
                $value
                    ->setTime(0, 0)
                    ->setTimezone(new \DateTimeZone('UTC'))
                    ->toAtomString()
            );
    }

    public function filterByProducer(\Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder $query, string $value): \Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder
    {
        if (empty($value)) {
            return $query;
        }

        $producer = (int)$value;
        return $query->where(function (\Jenssegers\Mongodb\Eloquent\Builder $query) use ($producer) {
            return $query->where('producers.mal_id', $producer)
                ->orWhere('licensors.mal_id', $producer)
                ->orWhere('studios.mal_id', $producer);
        });
    }

    /** @noinspection PhpUnused */
    public function filterByProducers(\Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder $query, string $value): \Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder
    {
        if (empty($value)) {
            return $query;
        }

        /* @var \Illuminate\Support\Collection $producers */
        $producers = collect(explode(',', $value))->filter();

        return $query->where(function (\Jenssegers\Mongodb\Eloquent\Builder $query) use ($producers) {
            $firstProducer = (int)$producers->first();
            $query = $query->where('producers.mal_id', $firstProducer)
                ->orWhere('licensors.mal_id', $firstProducer)
                ->orWhere('studios.mal_id', $firstProducer);

            foreach ($producers->skip(1) as $producer) {
                $producer = (int)$producer;
                $query = $query->orWhere('producers.mal_id', $producer)
                    ->orWhere('licensors.mal_id', $producer)
                    ->orWhere('studios.mal_id', $producer);
            }

            return $query;
        });
    }

    /** @noinspection PhpUnused */
    public function scopeExceptItemsWithAdultRating(\Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder $query): \Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder
    {
        return $query
            ->where("demographics.mal_id", "!=", Constants::GENRE_ANIME_HENTAI)
            ->where("demographics.mal_id", "!=", Constants::GENRE_ANIME_EROTICA)
            ->where("rating", "!=", AnimeRatingEnum::rx()->label)
            ->where("genres.mal_id", "!=", Constants::GENRE_ANIME_HENTAI);
    }

    /** @noinspection PhpUnused */
    public function scopeExceptKidsItems(\Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder $query): \Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder
    {
        return $query
            ->where("demographics.mal_id", "!=", Constants::GENRE_ANIME_KIDS);
    }

    /** @noinspection PhpUnused */
    public function scopeOnlyKidsItems(\Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder $query): \Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder
    {
        return $query
            ->where("demographics.mal_id", Constants::GENRE_ANIME_KIDS);
    }

    public static function scrape(int $id)
    {
        $data = app('JikanParser')->getAnime(new AnimeRequest($id));

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
            'id' => (string)$this->mal_id,
            'mal_id' => (int)$this->mal_id,
            'start_date' => $this->convertToTimestamp($this->aired['from']),
            'end_date' => $this->convertToTimestamp($this->aired['to']),
            'title' => $this->title,
            'title_transformed' => $this->simplifyStringForSearch($this->title),
            'title_english' => $this->title_english ?? "",
            'title_english_transformed' => $this->simplifyStringForSearch($this->title_english),
            'title_japanese' => $this->title_japanese,
            'title_japanese_transformed' => $this->simplifyStringForSearch($this->title_japanese),
            'title_synonyms' => collect($this->titles ?? [])
                                    ->filter(fn($v, $k) => !in_array($v["type"], ["Default", "English", "Japanese"]))
                                    ->pluck("title")
                                    ->values()
                                    ->all(),
            'type' => $this->type,
            'source' => $this->source,
            'episodes' => $this->episodes,
            'status' => $this->status,
            'airing' => $this->airing,
            'rating' => $this->rating,
            'score' => $this->score,
            'rank' => $this->rank,
            'popularity' => $this->popularity,
            'members' => $this->members,
            'favorites' => $this->favorites,
            'synopsis' => $this->synopsis,
            'season' => $this->season,
            'year' => $this->year,
            'approved' => $this->approved ?? false,
            'producers' => $this->getMalIdsOfField($this->producers),
            'studios' => $this->getMalIdsOfField($this->studios),
            'licensors' => $this->getMalIdsOfField($this->licensors),
            'genres' => $this->getMalIdsOfField($this->genres),
            'explicit_genres' => $this->getMalIdsOfField($this->explicit_genres),
            'themes' => $this->getMalIdsOfField($this->themes),
            'demographics' => $this->getMalIdsOfField($this->demographics)
        ];
    }

    public function getCollectionSchema(): array
    {
        return [
            'name' => $this->searchableAs(),
            'fields' => [
                [
                    'name' => '.*',
                    'type' => 'auto',
                ],
                [
                    'name' => 'title',
                    'type' => 'string',
                    'optional' => false,
                    'infix' => true,
                    'sort' => true
                ],
                [
                    'name' => 'title_transformed',
                    'type' => 'string',
                    'optional' => false,
                    'infix' => true,
                    'sort' => true
                ],
                [
                    'name' => 'title_japanese',
                    'type' => 'string',
                    'optional' => true,
                    'locale' => 'jp',
                    'infix' => true,
                    'sort' => false
                ],
                [
                    'name' => 'title_japanese_transformed',
                    'type' => 'string',
                    'optional' => true,
                    'locale' => 'jp',
                    'infix' => true,
                    'sort' => false
                ],
                [
                    'name' => 'title_english',
                    'type' => 'string',
                    'optional' => true,
                    'infix' => true,
                    'sort' => true
                ],
                [
                    'name' => 'title_english_transformed',
                    'type' => 'string',
                    'optional' => true,
                    'infix' => true,
                    'sort' => true
                ],
                [
                    'name' => 'title_synonyms',
                    'type' => 'string[]',
                    'optional' => true,
                    'infix' => true,
                    'sort' => false
                ]
            ]
        ];
    }

    /**
     * The fields to be queried against. See https://typesense.org/docs/0.21.0/api/documents.html#search.
     *
     * @return array
     */
    public function typesenseQueryBy(): array
    {
        return [
            'title',
            'title_transformed',
            'title_english',
            'title_english_transformed',
            'title_japanese',
            'title_japanese_transformed',
            'title_synonyms',
        ];
    }

    public function getTypeSenseQueryByWeights(): string|null
    {
        return "2,2,1,1,3,3,1";
    }

    /**
     * Returns which fields the search index should sort on when searching
     * @return array|null
     */
    public function getSearchIndexSortBy(): array|null
    {
        return [
            [
                "field" => "_text_match(buckets:".text_match_buckets().")",
                "direction" => "desc"
            ],
            [
                "field" => "popularity",
                "direction" => "asc"
            ],
            [
                "field" => "rank",
                "direction" => "asc"
            ]
        ];
    }

    private function adaptBroadcastValue(array|string|null|BSONDocument $broadcast): array
    {
        $null_value = [
            'day' => null,
            'time' => null,
            'timezone' => null,
            'string' => null
        ];
        if (is_null($broadcast)) {
            return $null_value;
        }

        if (is_array($broadcast)) {
            return $broadcast;
        }

        if ($broadcast instanceof BSONDocument) {
            return $broadcast->getArrayCopy();
        }

        if (!preg_match('~(.*) at (.*) \(~', $broadcast, $matches)) {
            return [
                'day' => null,
                'time' => null,
                'timezone' => null,
                'string' => $broadcast
            ];
        }

        if (preg_match('~(.*) at (.*) \(~', $broadcast, $matches)) {
            return [
                'day' => $matches[1],
                'time' => $matches[2],
                'timezone' => 'Asia/Tokyo',
                'string' => $broadcast
            ];
        }

        return $null_value;
    }

    protected static function newFactory()
    {
        return App::make(AnimeFactory::class);
    }
}
