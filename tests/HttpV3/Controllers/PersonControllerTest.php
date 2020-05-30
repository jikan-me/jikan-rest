<?php

class PersonControllerTest extends TestCase
{
    public function testMain()
    {
        $this->get('/v3/person/1')
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'mal_id',
                'url',
                'image_url',
                'website_url',
                'name',
                'given_name',
                'family_name',
                'alternate_names',
                'birthday',
                'about',
                'member_favorites',
                'voice_acting_roles' => [
                    [
                        'role',
                        'anime' => [
                            'mal_id',
                            'url',
                            'image_url',
                            'name'
                        ],
                        'character' => [
                            'mal_id',
                            'url',
                            'image_url',
                            'name'
                        ]
                    ]
                ],
                'anime_staff_positions' => [
                    [
                        'position',
                        'anime' => [
                            'mal_id',
                            'url',
                            'image_url',
                            'name'
                        ],
                    ]
                ],
                'published_manga' => []
            ]);
    }

    public function testPictures()
    {
        $this->get('/v3/person/1/pictures')
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'pictures' => [
                    [
                        'large',
                        'small',
                    ]
                ]
            ]);
    }

    public function test404()
    {
        $this->get('/v3/person/1000000')
            ->seeStatusCode(404);
    }
}
