<?php /** @noinspection PhpIllegalPsrClassPathInspection */
namespace Tests\Integration;
use App\Club;
use App\Testing\ScoutFlush;
use App\Testing\SyntheticMongoDbTransaction;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ClubControllerTest extends TestCase
{
    use SyntheticMongoDbTransaction;
    use ScoutFlush;

    public function testMain()
    {
        Club::factory()->createOne([
            "mal_id" => 1
        ]);
        $this->get('/v4/clubs/1')
            ->seeStatusCode(200)
            ->seeJsonStructure(['data'=>[
                'mal_id',
                'name',
                'url',
                'images' => [
                    'jpg' => [
                        'image_url',
                    ],
                ],
                'members',
                'category',
                'created',
                'access',
            ]]);
    }

    public function testMembers()
    {
        $dummyUsername = $this->faker->userName();
        $document = $this->dummyResultsDocument('/v4/clubs/1/members', 'clubs', [
            [
                "username" => $this->faker->userName(),
                "url" => "https://myanimelist.net/profile/".$dummyUsername,
                "images" => [
                    "jpg" => [
                        "image_url" => "http://httpbin.org/get"
                    ],
                    "webp" => [
                        "image_url" => "http://httpbin.org/get"
                    ]
                ]
            ]
        ]);
        DB::table("clubs_members")->insert($document);
        $this->get('/v4/clubs/1/members')
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'pagination' => [
                    'last_visible_page',
                    'has_next_page',
                ],
                'data' => [
                    [
                        'username',
                        'url',
                        'images' => [
                            'jpg' => [
                                'image_url'
                            ],
                            'webp' => [
                                'image_url'
                            ]
                        ],
                    ]
                ]
            ]);
    }

    public function test404()
    {
        $this->mockJikanParserWith404RespondingUpstream();
        $this->get('/v4/clubs/1000000')
            ->seeStatusCode(404);
    }
}
