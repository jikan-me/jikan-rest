<?php /** @noinspection PhpIllegalPsrClassPathInspection */
namespace Tests\HttpV4\Controllers;
use tests\TestCase;

class ClubControllerTest extends TestCase
{
    public function testMain()
    {
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
        $this->get('/v4/clubs/1000000')
            ->seeStatusCode(404);
    }
}
