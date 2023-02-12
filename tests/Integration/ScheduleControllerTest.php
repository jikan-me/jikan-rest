<?php /** @noinspection PhpIllegalPsrClassPathInspection */
namespace Tests\Integration;
use App\Anime;
use App\Enums\AnimeStatusEnum;
use App\Enums\AnimeTypeEnum;
use App\Testing\ScoutFlush;
use App\Testing\SyntheticMongoDbTransaction;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Tests\TestCase;

class ScheduleControllerTest extends TestCase
{
    use SyntheticMongoDbTransaction;
    use ScoutFlush;

    public function testSchedule()
    {
        Anime::factory(3)->state(new Sequence(
            ["members" => 100],
            ["members" => 50],
            ["members" => 10]
        ))->state([
            "type" => AnimeTypeEnum::tv()->label,
            "status" => AnimeStatusEnum::airing()->label,
            "airing" => true,
        ])->create();
        $this->get('/v4/schedules')
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'data' => [
                    [
                        'mal_id',
                        'url',
                        'images' => [
                            'jpg' => [
                                'image_url',
                                'small_image_url',
                                'large_image_url'
                            ],
                            'webp' => [
                                'image_url',
                                'small_image_url',
                                'large_image_url'
                            ],
                        ],
                        'trailer' => [
                            'youtube_id',
                            'url',
                            'embed_url',
                            'images' => [
                                'image_url',
                                'small_image_url',
                                'medium_image_url',
                                'large_image_url',
                                'maximum_image_url',
                            ]
                        ],
                        'title',
                        'title_english',
                        'title_japanese',
                        'title_synonyms',
                        'type',
                        'source',
                        'episodes',
                        'status',
                        'airing',
                        'aired' => [
                            'from',
                            'to',
                            'prop' => [
                                'from' => [
                                    'day',
                                    'month',
                                    'year'
                                ],
                                'to' => [
                                    'day',
                                    'month',
                                    'year'
                                ]
                            ],
                            'string'
                        ],
                        'duration',
                        'rating',
                        'score',
                        'scored_by',
                        'rank',
                        'popularity',
                        'members',
                        'favorites',
                        'synopsis',
                        'background',
                        'season',
                        'year',
                        'broadcast' => [
                            'day',
                            'time',
                            'timezone',
                            'string'
                        ],
                        'producers',
                        'licensors',
                        'studios',
                        'genres',
                    ]
                ]
            ]);
    }

    public function test400()
    {
        $this->get('/v4/schedules/asdjkhas')
            ->seeStatusCode(400);
    }
}
