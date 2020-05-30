<?php

class CharacterControllerTest extends TestCase
{
    public function testMain()
    {
        $this->get('/v3/character/1')
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'mal_id',
                'url',
                'image_url',
                'name',
                'name_kanji',
                'nicknames',
                'about',
                'member_favorites',
                'animeography' => [
                    [
                        'mal_id',
                        'name',
                        'url',
                        'image_url',
                        'role'
                    ]
                ],
                'mangaography' => [
                    [
                        'mal_id',
                        'name',
                        'url',
                        'image_url',
                        'role'
                    ]
                ],
                'voice_actors' => [
                    [
                        'mal_id',
                        'name',
                        'url',
                        'image_url',
                        'language'
                    ]
                ]
            ]);
    }

    public function testPictures()
    {
        $this->get('/v3/character/1/pictures')
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
        $this->get('/v3/character/1000000')
            ->seeStatusCode(404);
    }
}
