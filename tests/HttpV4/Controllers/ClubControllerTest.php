<?php /** @noinspection PhpIllegalPsrClassPathInspection */
namespace Tests\HttpV4\Controllers;
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
        $t = $this->get('/v4/clubs/1');
            $t->seeStatusCode(200)
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
        $m = Club::factory()->createOne([
            "mal_id" => 1
        ]);
        $dummyUsername = $this->faker->userName();
        DB::table("clubs_members")->insert([
            // we are just copying the data from the manufactured model out of convenience
            "createdAt" => $m->createdAt,
            "modifiedAt" => $m->modifiedAt,
            "has_next_page" => false,
            "last_visible_page" => 1,
            "request_hash" => "request:clubs:".sha1("/v4/clubs/1/members"),
            "results" => [
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
            ]
        ]);
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

        $this->get('/v4/clubs/1000000/members')
            ->seeStatusCode(404);
    }

    public function test404()
    {
        $this->mockJikanParserWith404RespondingUpstream();
        $this->get('/v4/clubs/1000000')
            ->seeStatusCode(404);
    }
}
