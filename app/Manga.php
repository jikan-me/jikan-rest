<?php

namespace App;

use App\Concerns\FilteredByLetter;
use App\Concerns\MediaFilters;
use App\Enums\MangaTypeEnum;
use App\Support\HttpHelper;
use Carbon\CarbonImmutable;
use Database\Factories\MangaFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\App;
use Jikan\Helper\Constants;
use Jikan\Request\Manga\MangaRequest;
use Laravel\Scout\Builder;
use MongoDB\BSON\UTCDateTime;

/**
 * Mongo-backed manga document. Attributes are magic (from the collection); the
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
 * @property int|null $chapters
 * @property int|null $volumes
 * @property string $status
 * @property bool|null $publishing
 * @property array<string, mixed>|null $published
 * @property string|null $duration
 * @property float|null $score
 * @property int|null $scored_by
 * @property int|null $rank
 * @property int|null $popularity
 * @property int|null $members
 * @property int|null $favorites
 * @property string|null $synopsis
 * @property string|null $background
 * @property array<string, mixed> $related
 * @property array<string, mixed> $authors
 * @property array<string, mixed> $serializations
 * @property array<string, mixed> $magazines
 * @property array<string, mixed> $genres
 * @property array<string, mixed> $explicit_genres
 * @property array<string, mixed> $demographics
 * @property array<string, mixed>|null $external_links
 * @property UTCDateTime|null $createdAt
 * @property UTCDateTime|null $modifiedAt
 *
 * @use HasFactory<MangaFactory>
 */
class Manga extends JikanApiSearchableModel
{
    use FilteredByLetter, HasFactory, MediaFilters;

    protected array $filters = [
        'order_by', 'status', 'type', 'sort', 'max_score', 'min_score', 'score', 'start_date', 'end_date', 'magazine',
        'magazines', 'letter', 'genres', 'genres_exclude', 'sfw', 'unapproved', 'kids',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'mal_id', 'url', 'title', 'title_english', 'title_japanese', 'title_synonyms', 'titles',
        'images', 'status', 'type', 'volumes', 'chapters', 'publishing', 'published', 'rank', 'score',
        'scored_by', 'popularity', 'members', 'favorites', 'synopsis', 'background', 'related',
        'genres', 'explicit_genres', 'themes', 'demographics', 'authors', 'serializations',
        'createdAt', 'modifiedAt', 'approved',
        'external_links',

    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var list<string>
     */
    protected $appends = ['themes'];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'manga';

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var list<string>
     */
    protected $hidden = [
        '_id', 'expiresAt', 'request_hash',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->displayNameFieldName = 'title';
    }

    /**
     * @param  Builder<static>|\Illuminate\Database\Eloquent\Builder<static>  $query
     * @return Builder<static>|\Illuminate\Database\Eloquent\Builder<static>
     *
     * @noinspection PhpUnused
     */
    public function filterByStartDate(Builder|\Illuminate\Database\Eloquent\Builder $query, CarbonImmutable $value): Builder|\Illuminate\Database\Eloquent\Builder
    {
        return $query
            ->where('published.from', '>=',
                $value->setTime(0, 0)
                    ->setTimezone(new \DateTimeZone('UTC'))
                    ->toAtomString()
            );
    }

    /** @noinspection PhpUnused */
    /**
     * @param  Builder<static>|\Illuminate\Database\Eloquent\Builder<static>  $query
     * @return Builder<static>|\Illuminate\Database\Eloquent\Builder<static>
     */
    public function filterByType(Builder|\Illuminate\Database\Eloquent\Builder $query, MangaTypeEnum $value): Builder|\Illuminate\Database\Eloquent\Builder
    {
        return $query->where('type', $value->label);
    }

    /** @noinspection PhpUnused */
    /**
     * @param  Builder<static>|\Illuminate\Database\Eloquent\Builder<static>  $query
     * @return Builder<static>|\Illuminate\Database\Eloquent\Builder<static>
     */
    public function filterByEndDate(Builder|\Illuminate\Database\Eloquent\Builder $query, CarbonImmutable $value): Builder|\Illuminate\Database\Eloquent\Builder
    {
        return $query
            ->where('published.to', '<=',
                $value->setTime(0, 0)
                    ->setTimezone(new \DateTimeZone('UTC'))
                    ->toAtomString()
            );
    }

    /**
     * @param  Builder<static>|\Illuminate\Database\Eloquent\Builder<static>  $query
     * @return Builder<static>|\Illuminate\Database\Eloquent\Builder<static>
     */
    public function filterByMagazine(Builder|\Illuminate\Database\Eloquent\Builder $query, string $value): Builder|\Illuminate\Database\Eloquent\Builder
    {
        if (empty($value)) {
            return $query;
        }

        $magazine = (int) $value;

        return $query
            ->where('serializations.mal_id', $magazine);
    }

    /** @noinspection PhpUnused */
    /**
     * @param  Builder<static>|\Illuminate\Database\Eloquent\Builder<static>  $query
     * @return Builder<static>|\Illuminate\Database\Eloquent\Builder<static>
     */
    public function filterByMagazines(Builder|\Illuminate\Database\Eloquent\Builder $query, string $value): Builder|\Illuminate\Database\Eloquent\Builder
    {
        if (empty($value)) {
            return $query;
        }

        /** @var array<int, int> $magazines */
        $magazines = collect(explode(',', $value))->filter()->map(fn ($x) => (int) $x)->toArray();

        return $query->whereIn('serializations.mal_id', $magazines);
    }

    /** @noinspection PhpUnused */
    /**
     * @param  Builder<static>|\Illuminate\Database\Eloquent\Builder<static>  $query
     * @return Builder<static>|\Illuminate\Database\Eloquent\Builder<static>
     */
    public function scopeExceptItemsWithAdultRating(Builder|\Illuminate\Database\Eloquent\Builder $query): Builder|\Illuminate\Database\Eloquent\Builder
    {
        return $query
            ->where('type', '!=', MangaTypeEnum::doujin()->label)
            ->where('demographics.mal_id', '!=', Constants::GENRE_MANGA_HENTAI)
            ->where('demographics.mal_id', '!=', Constants::GENRE_MANGA_EROTICA)
            ->where('genres.mal_id', '!=', Constants::GENRE_MANGA_HENTAI);
    }

    /** @noinspection PhpUnused */
    /**
     * @param  Builder<static>|\Illuminate\Database\Eloquent\Builder<static>  $query
     * @return Builder<static>|\Illuminate\Database\Eloquent\Builder<static>
     */
    public function scopeExceptKidsItems(Builder|\Illuminate\Database\Eloquent\Builder $query): Builder|\Illuminate\Database\Eloquent\Builder
    {
        return $query
            ->where('demographics.mal_id', '!=', Constants::GENRE_MANGA_KIDS);
    }

    /** @noinspection PhpUnused */
    /**
     * @param  Builder<static>|\Illuminate\Database\Eloquent\Builder<static>  $query
     * @return Builder<static>|\Illuminate\Database\Eloquent\Builder<static>
     */
    public function scopeOnlyKidsItems(Builder|\Illuminate\Database\Eloquent\Builder $query): Builder|\Illuminate\Database\Eloquent\Builder
    {
        return $query
            ->where('demographics.mal_id', Constants::GENRE_MANGA_KIDS);
    }

    /**
     * @return array<string, mixed>
     */
    public static function scrape(int $id): array
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
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => (string) $this->mal_id,
            'mal_id' => (int) $this->mal_id,
            'start_date' => $this->convertToTimestamp($this->published['from']),
            'end_date' => $this->convertToTimestamp($this->published['to']),
            'title' => $this->title,
            'title_transformed' => $this->simplifyStringForSearch($this->title),
            'title_english' => $this->title_english ?? '',
            'title_english_transformed' => $this->simplifyStringForSearch($this->title_english),
            'title_japanese' => $this->title_japanese,
            'title_japanese_transformed' => $this->simplifyStringForSearch($this->title_japanese),
            'title_synonyms' => collect($this->titles ?? [])
                ->filter(fn ($v, $k) => ! in_array($v['type'], ['Default', 'English', 'Japanese']))
                ->pluck('title')
                ->values()
                ->all(),
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
            'approved' => $this->approved ?? false,
            'magazines' => $this->getMalIdsOfField($this->magazines),
            'genres' => $this->getMalIdsOfField($this->genres),
            'explicit_genres' => $this->getMalIdsOfField($this->explicit_genres),
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
            ],
        ];
    }

    /** @noinspection PhpUnused */
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
     */
    /**
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

    protected static function newFactory(): Factory
    {
        return App::make(MangaFactory::class);
    }
}
