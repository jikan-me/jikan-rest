<?php

namespace App;

use App\Concerns\FilteredByLetter;
use App\Concerns\MediaFilters;
use App\Enums\AnimeRatingEnum;
use App\Enums\AnimeTypeEnum;
use App\Support\HttpHelper;
use Carbon\CarbonImmutable;
use Database\Factories\AnimeFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Jikan\Helper\Constants;
use Jikan\Request\Anime\AnimeRequest;
use Laravel\Scout\Builder;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Model\BSONDocument;

/**
 * Mongo-backed anime document. Attributes are magic (from the collection); the
 * documented ones are those the app/resources actually read.
 *
 * @property int $mal_id
 * @property string $url
 * @property string $title
 * @property string|null $title_english
 * @property string|null $title_japanese
 * @property array<string, mixed> $title_synonyms
 * @property array<string, mixed> $titles
 * @property array<string, mixed> $images
 * @property string $type
 * @property string|null $source
 * @property int|null $episodes
 * @property string $status
 * @property bool|null $airing
 * @property array<string, mixed>|null $aired
 * @property string|null $duration
 * @property string|null $rating
 * @property float|null $score
 * @property int|null $scored_by
 * @property int|null $rank
 * @property int|null $popularity
 * @property int|null $members
 * @property int|null $favorites
 * @property string|null $synopsis
 * @property string|null $background
 * @property string|null $premiered
 * @property array<string, mixed>|null $broadcast
 * @property array<string, mixed> $related
 * @property array<string, mixed> $producers
 * @property array<string, mixed> $licensors
 * @property array<string, mixed> $studios
 * @property array<string, mixed> $genres
 * @property array<string, mixed> $explicit_genres
 * @property array<string, mixed> $themes
 * @property array<string, mixed> $demographics
 * @property array<string, mixed> $opening_themes
 * @property array<string, mixed> $ending_themes
 * @property array<string, mixed>|null $trailer
 * @property array<string, mixed>|null $streaming
 * @property array<string, mixed>|null $streaming_links
 * @property array<string, mixed>|null $external_links
 * @property array<string, mixed>|null $theme
 * @property array<string, mixed>|null $relations
 * @property bool|null $approved
 * @property string $season
 * @property int|null $year
 * @property UTCDateTime|null $createdAt
 * @property UTCDateTime|null $modifiedAt
 *
 * @use HasFactory<AnimeFactory>
 */
class Anime extends JikanApiSearchableModel
{
    use FilteredByLetter, HasFactory, MediaFilters;

    protected array $filters = [
        'order_by', 'status', 'type', 'sort', 'max_score', 'min_score', 'score', 'rating', 'start_date', 'end_date',
        'producer', 'producers', 'letter', 'genres', 'genres_exclude', 'sfw', 'unapproved', 'kids',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'mal_id', 'url', 'title', 'title_english', 'title_japanese', 'title_synonyms',
        'titles', 'images', 'type', 'source', 'episodes', 'status', 'airing', 'aired',
        'duration', 'rating', 'score', 'scored_by', 'rank', 'popularity', 'members',
        'favorites', 'synopsis', 'background', 'premiered', 'broadcast', 'related',
        'producers', 'licensors', 'studios', 'genres', 'explicit_genres', 'themes',
        'demographics', 'opening_themes', 'ending_themes', 'trailer', 'approved', 'createdAt', 'modifiedAt',
        'external_links', 'streaming_links',

    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var list<string>
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
     * @var list<string>
     */
    protected $hidden = [
        '_id', 'premiered', 'request_hash', 'expiresAt',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->displayNameFieldName = 'title';
    }

    public function setSeasonAttribute(mixed $value): void
    {
        // noop
        // this attribute is calculated
    }

    public function getSeasonAttribute(): ?string
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

    public function setYearAttribute(mixed $value): void
    {
        // noop
        // this attribute is calculated
    }

    public function getYearAttribute(): ?int
    {
        $premiered = array_key_exists('premiered', $this->attributes) ? $this->attributes['premiered'] : null;

        if (empty($premiered)
            || is_null($premiered)
            || !is_string($premiered)
            || !preg_match('~(Winter|Spring|Summer|Fall|)\s([\d+]{4})~', $premiered)
        ) {
            return null;
        }

        return (int) explode(' ', $premiered)[1];
    }

    public function setBroadcastAttribute(mixed $value): void
    {
        $this->attributes['broadcast'] = $this->adaptBroadcastValue($value);
    }

    /**
     * @return array<string, mixed>
     */
    public function getBroadcastAttribute(): array
    {
        if (array_key_exists('broadcast', $this->attributes)) {
            return $this->adaptBroadcastValue($this->attributes['broadcast']);
        }

        return [
            'day' => null,
            'time' => null,
            'timezone' => null,
            'string' => null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getThemesAttribute(): array
    {
        $result = [];
        if (array_key_exists('themes', $this->attributes)) {
            $result = $this->attributes['themes'];
        }

        return $result;
    }

    /** @noinspection PhpUnused */
    /**
     * @param  Builder<static>|\Illuminate\Database\Eloquent\Builder<static>  $query
     * @return Builder<static>|\Illuminate\Database\Eloquent\Builder<static>
     */
    public function filterByType(Builder|\Illuminate\Database\Eloquent\Builder $query, AnimeTypeEnum $value): Builder|\Illuminate\Database\Eloquent\Builder
    {
        return $query->where('type', $value->label);
    }

    /** @noinspection PhpUnused */
    /**
     * @param  Builder<static>|\Illuminate\Database\Eloquent\Builder<static>  $query
     * @return Builder<static>|\Illuminate\Database\Eloquent\Builder<static>
     */
    public function filterByRating(Builder|\Illuminate\Database\Eloquent\Builder $query, AnimeRatingEnum $value): Builder|\Illuminate\Database\Eloquent\Builder
    {
        return $query->where('rating', $value->label);
    }

    /** @noinspection PhpUnused */
    /**
     * @param  Builder<static>|\Illuminate\Database\Eloquent\Builder<static>  $query
     * @return Builder<static>|\Illuminate\Database\Eloquent\Builder<static>
     */
    public function filterByStartDate(Builder|\Illuminate\Database\Eloquent\Builder $query, CarbonImmutable $value): Builder|\Illuminate\Database\Eloquent\Builder
    {
        $compareVal = $value->setTime(0, 0)->setTimezone(new \DateTimeZone('UTC'));
        if (is_scout_query_builder($query)) {
            $query = $query->where('start_date', ['>=', $compareVal->getTimestamp()]);
        } else {
            $query = $query->where('aired.from', '>=', $compareVal->toAtomString());
        }

        return $query;
    }

    /** @noinspection PhpUnused */
    /**
     * @param  Builder<static>|\Illuminate\Database\Eloquent\Builder<static>  $query
     * @return Builder<static>|\Illuminate\Database\Eloquent\Builder<static>
     */
    public function filterByEndDate(Builder|\Illuminate\Database\Eloquent\Builder $query, CarbonImmutable $value): Builder|\Illuminate\Database\Eloquent\Builder
    {
        $compareVal = $value->setTime(0, 0)->setTimezone(new \DateTimeZone('UTC'));
        if (is_scout_query_builder($query)) {
            $query = $query->where('(end_date', [
                '<=', $compareVal->getTimestamp(),
                ' || end_date:=', 'null',
                ')',
            ]);
        } else {
            $query = $query->where(function ($query) use ($compareVal) {
                return $query->where('aired.to', '<=', $compareVal->toAtomString())
                    ->orWhere('aired.to', null);
            });
        }

        return $query;
    }

    /**
     * @param  Builder<static>|\Illuminate\Database\Eloquent\Builder<static>  $query
     * @return Builder<static>|\Illuminate\Database\Eloquent\Builder<static>
     */
    public function filterByProducer(Builder|\Illuminate\Database\Eloquent\Builder $query, string $value): Builder|\Illuminate\Database\Eloquent\Builder
    {
        if (empty($value)) {
            return $query;
        }

        $producer = (int) $value;

        if (is_scout_query_builder($query)) {
            $query = $query->where('(producers', [
                '=', $producer,
                ' || licensors:=', $producer,
                ' || studios:=', $producer,
                ')',
            ]);
        } else {
            $query = $query->where(function ($query) use ($producer) {
                return $query->where('producers.mal_id', $producer)
                    ->orWhere('licensors.mal_id', $producer)
                    ->orWhere('studios.mal_id', $producer);
            });
        }

        return $query;
    }

    /** @noinspection PhpUnused */
    /**
     * @param  Builder<static>|\Illuminate\Database\Eloquent\Builder<static>  $query
     * @return Builder<static>|\Illuminate\Database\Eloquent\Builder<static>
     */
    public function filterByProducers(Builder|\Illuminate\Database\Eloquent\Builder $query, string $value): Builder|\Illuminate\Database\Eloquent\Builder
    {
        if (empty($value)) {
            return $query;
        }

        $producers = collect(explode(',', $value))->filter();

        if (is_scout_query_builder($query)) {
            $producerFilterList = '['.$producers->implode(', ').']';
            $query = $query->where('(producers', [
                '=', $producerFilterList,
                ' || licensors:=', $producerFilterList,
                ' || studios:=', $producerFilterList,
                ')',
            ]);
        } else {
            $query = $query->where(function ($query) use ($producers) {
                $firstProducer = (int) $producers->first();
                $query = $query->where('producers.mal_id', $firstProducer)
                    ->orWhere('licensors.mal_id', $firstProducer)
                    ->orWhere('studios.mal_id', $firstProducer);

                foreach ($producers->skip(1) as $producer) {
                    $producer = (int) $producer;
                    $query = $query->orWhere('producers.mal_id', $producer)
                        ->orWhere('licensors.mal_id', $producer)
                        ->orWhere('studios.mal_id', $producer);
                }

                return $query;
            });
        }

        return $query;
    }

    /** @noinspection PhpUnused */
    /**
     * @param  Builder<static>|\Illuminate\Database\Eloquent\Builder<static>  $query
     * @return Builder<static>|\Illuminate\Database\Eloquent\Builder<static>
     */
    public function scopeExceptItemsWithAdultRating(Builder|\Illuminate\Database\Eloquent\Builder $query): Builder|\Illuminate\Database\Eloquent\Builder
    {
        if (is_scout_query_builder($query)) {
            return $query->where('sfw', true);
        }

        foreach ($this->sfwExclusions() as $field => $excludedValue) {
            $query = $query->where($field, '!=', $excludedValue);
        }

        return $query;
    }

    /**
     * Erotica is intentionally allowed: those titles are suggestive (16+ in most jurisdictions), not explicit.
     *
     * @return array<string, string|int>
     */
    public function sfwExclusions(): array
    {
        return [
            'rating' => AnimeRatingEnum::rx()->label,
            'genres.mal_id' => Constants::GENRE_ANIME_HENTAI,
        ];
    }

    /**
     * Must stay equivalent to sfwExclusions(); its result is indexed as "sfw" for the search engine.
     */
    public function isSfw(): bool
    {
        return $this->rating !== AnimeRatingEnum::rx()->label
            && !in_array(Constants::GENRE_ANIME_HENTAI, $this->getMalIdsOfField($this->genres));
    }

    /** @noinspection PhpUnused */
    /**
     * @param  Builder<static>|\Illuminate\Database\Eloquent\Builder<static>  $query
     * @return Builder<static>|\Illuminate\Database\Eloquent\Builder<static>
     */
    public function scopeExceptKidsItems(Builder|\Illuminate\Database\Eloquent\Builder $query): Builder|\Illuminate\Database\Eloquent\Builder
    {
        if (is_scout_query_builder($query)) {
            $query = $query->where('demographics', ['!=', Constants::GENRE_ANIME_KIDS]);
        } else {
            $query = $query->where('demographics.mal_id', '!=', Constants::GENRE_ANIME_KIDS);
        }

        return $query;
    }

    /** @noinspection PhpUnused */
    /**
     * @param  Builder<static>|\Illuminate\Database\Eloquent\Builder<static>  $query
     * @return Builder<static>|\Illuminate\Database\Eloquent\Builder<static>
     */
    public function scopeOnlyKidsItems(Builder|\Illuminate\Database\Eloquent\Builder $query): Builder|\Illuminate\Database\Eloquent\Builder
    {
        if (is_scout_query_builder($query)) {
            $query = $query->where('demographics', Constants::GENRE_ANIME_KIDS);
        } else {
            $query = $query->where('demographics.mal_id', Constants::GENRE_ANIME_KIDS);
        }

        return $query;
    }

    /**
     * @return array<string, mixed>
     */
    public static function scrape(int $id): array
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
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => (string) $this->mal_id,
            'mal_id' => (int) $this->mal_id,
            'start_date' => $this->convertToTimestamp($this->aired['from'] ?? null),
            'end_date' => $this->convertToTimestamp($this->aired['to'] ?? null),
            'title' => $this->title,
            'title_transformed' => $this->simplifyStringForSearch($this->title),
            'title_english' => $this->title_english ?? '',
            'title_english_transformed' => $this->simplifyStringForSearch($this->title_english),
            'title_japanese' => $this->title_japanese,
            'title_japanese_transformed' => $this->simplifyStringForSearch($this->title_japanese),
            'title_synonyms' => collect($this->titles ?? [])
                ->filter(fn ($v, $k) => !in_array($v['type'], ['Default', 'English', 'Japanese']))
                ->pluck('title')
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
            'demographics' => $this->getMalIdsOfField($this->demographics),
            'sfw' => $this->isSfw(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
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
                    'sort' => true,
                ],
                [
                    'name' => 'title_transformed',
                    'type' => 'string',
                    'optional' => false,
                    'infix' => true,
                    'sort' => true,
                ],
                [
                    'name' => 'title_japanese',
                    'type' => 'string',
                    'optional' => true,
                    'locale' => 'jp',
                    'infix' => true,
                    'sort' => false,
                ],
                [
                    'name' => 'title_japanese_transformed',
                    'type' => 'string',
                    'optional' => true,
                    'locale' => 'jp',
                    'infix' => true,
                    'sort' => false,
                ],
                [
                    'name' => 'title_english',
                    'type' => 'string',
                    'optional' => true,
                    'infix' => true,
                    'sort' => true,
                ],
                [
                    'name' => 'title_english_transformed',
                    'type' => 'string',
                    'optional' => true,
                    'infix' => true,
                    'sort' => true,
                ],
                [
                    'name' => 'title_synonyms',
                    'type' => 'string[]',
                    'optional' => true,
                    'infix' => true,
                    'sort' => false,
                ],
                [
                    'name' => 'sfw',
                    'type' => 'bool',
                    'optional' => true,
                ],
            ],
        ];
    }

    /**
     * The fields to be queried against. See https://typesense.org/docs/0.21.0/api/documents.html#search.
     *
     * @return string[]
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

    public function getTypeSenseQueryByWeights(): ?string
    {
        return '2,2,1,1,3,3,1';
    }

    /**
     * Returns which fields the search index should sort on when searching
     *
     * @return array<int, array<string, string>>|null
     */
    public function getSearchIndexSortBy(): ?array
    {
        return [
            [
                'field' => '_text_match(buckets:'.text_match_buckets().')',
                'direction' => 'desc',
            ],
            [
                'field' => 'popularity',
                'direction' => 'asc',
            ],
            [
                'field' => 'rank',
                'direction' => 'asc',
            ],
        ];
    }

    /**
     * @param  array<string, mixed>|string|null|BSONDocument  $broadcast
     * @return array<string, mixed>
     */
    private function adaptBroadcastValue(array|string|null|BSONDocument $broadcast): array
    {
        $null_value = [
            'day' => null,
            'time' => null,
            'timezone' => null,
            'string' => null,
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
                'string' => $broadcast,
            ];
        }

        if (preg_match('~(.*) at (.*) \(~', $broadcast, $matches)) {
            return [
                'day' => $matches[1],
                'time' => $matches[2],
                'timezone' => 'Asia/Tokyo',
                'string' => $broadcast,
            ];
        }

        return $null_value;
    }

    protected static function newFactory(): Factory
    {
        return App::make(AnimeFactory::class);
    }
}
