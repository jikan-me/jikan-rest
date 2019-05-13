<?php

class ClubControllerTest extends TestCase
{
    public function testMain()
    {
        $this->get('/v3/club/1')
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'mal_id',
                'url',
                'image_url',
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
                'anime_relations' => [
                    [
                        'mal_id',
                        'type',
                        'name',
                        'url',
                    ]
                ],
                'manga_relations' => [
                    [
                        'mal_id',
                        'type',
                        'name',
                        'url',
                    ]
                ],
                'character_relations' => [
                    [
                        'mal_id',
                        'type',
                        'name',
                        'url',
                    ]
                ],
            ]);
    }

    public function testMembers()
    {
        $this->get('/v3/club/1/members')
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'members' => [
                    [
                        'username',
                        'url',
                        'image_url'
                    ]
                ]
            ]);

        $this->get('/v3/club/1/members/1000')
            ->seeStatusCode(404);
    }

    public function test404()
    {
        $this->get('/v3/club/1000000')
            ->seeStatusCode(404);
    }
}
