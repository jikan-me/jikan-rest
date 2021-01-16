<?php

class ClubControllerTest extends TestCase
{
    public function testMain()
    {
        $this->get('/v4/clubs/1')
            ->seeStatusCode(200)
            ->seeJsonStructure(['data'=>[
                'mal_id',
                'url',
                'images',
                'title',
                'members_count',
                'pictures_count',
                'category',
                'created',
                'type',
                'staff' => [
                    [
                        'mal_id',
                        'type',
                        'name',
                        'url',
                    ]
                ],
                'anime' => [
                    [
                        'mal_id',
                        'type',
                        'name',
                        'url',
                    ]
                ],
                'manga' => [
                    [
                        'mal_id',
                        'type',
                        'name',
                        'url',
                    ]
                ],
                'characters' => [
                    [
                        'mal_id',
                        'type',
                        'name',
                        'url',
                    ]
                ],
            ]]);
    }

    public function testMembers()
    {
        $this->get('/v4/club/1/members')
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'pagination' => [
                    'last_visible_page',
                    'hast_next_page',
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

        $this->get('/v4/club/1/members/1000')
            ->seeStatusCode(404);
    }

    public function test404()
    {
        $this->get('/v4/clubs/1000000')
            ->seeStatusCode(404);
    }
}
