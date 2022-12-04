<?php

namespace App;

use App\Http\HttpHelper;
use Jikan\Jikan;
use Jikan\Request\Anime\AnimeRequest;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Anime extends JikanApiSearchableModel
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
        'mal_id','url','title','title_english','title_japanese','title_synonyms', 'titles', 'images', 'type','source','episodes','status','airing','aired','duration','rating','score','scored_by','rank','popularity','members','favorites','synopsis','background','premiered','broadcast','related','producers','licensors','studios','genres', 'explicit_genres', 'themes', 'demographics', 'opening_themes','ending_themes'
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
        '_id', 'premiered', 'opening_themes', 'ending_themes', 'request_hash', 'expiresAt'
    ];

    public function setSeasonAttribute($value)
    {
        $this->attributes['season'] = $this->getSeasonAttribute();
    }

    public function getSeasonAttribute()
    {
        $premiered = $this->attributes['premiered'];

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
        $this->attributes['year'] = $this->getYearAttribute();
    }

    public function getYearAttribute()
    {
        $premiered = $this->attributes['premiered'];

        if (empty($premiered)
            || is_null($premiered)
            || !preg_match('~(Winter|Spring|Summer|Fall|)\s([\d+]{4})~', $premiered)
        ) {
            return null;
        }

        return (int) explode(' ', $premiered)[1];
    }

    public function setBroadcastAttribute($value)
    {
        $this->attributes['year'] = $this->getBroadcastAttribute();
    }

    public function getBroadcastAttribute()
    {
        if (array_key_exists("broadcast", $this->attributes)) {
            $broadcastStr = $this->attributes['broadcast'];

            if (!preg_match('~(.*) at (.*) \(~', $broadcastStr, $matches)) {
                return [
                    'day' => null,
                    'time' => null,
                    'timezone' => null,
                    'string' => $broadcastStr
                ];
            }

            if (preg_match('~(.*) at (.*) \(~', $broadcastStr, $matches)) {
                return [
                    'day' => $matches[1],
                    'time' => $matches[2],
                    'timezone' => 'Asia/Tokyo',
                    'string' => $broadcastStr
                ];
            }
        }

        return [
            'day' => null,
            'time' => null,
            'timezone' => null,
            'string' => null
        ];
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
            'id' => (string) $this->mal_id,
            'mal_id' => (string) $this->mal_id,
            'start_date' => $this->convertToTimestamp($this->aired['from']),
            'end_date' => $this->convertToTimestamp($this->aired['to']),
            'title' => $this->title,
            'title_transformed' => $this->simplifyStringForSearch($this->title),
            'title_english' => $this->title_english ?? "",
            'title_english_transformed' => $this->simplifyStringForSearch($this->title_english),
            'title_japanese' => $this->title_japanese,
            'title_japanese_transformed' => $this->simplifyStringForSearch($this->title_japanese),
            'title_synonyms' => $this->title_synonyms,
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
            'producers' => $this->getMalIdsOfField($this->producers),
            'studios' => $this->getMalIdsOfField($this->studios),
            'licensors' => $this->getMalIdsOfField($this->licensors),
            'genres' => $this->getMalIdsOfField($this->genres),
            'explicit_genres' => $this->getMalIdsOfField($this->explicit_genres)
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
}
