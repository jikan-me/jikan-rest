<?php

class SearchControllerTest extends TestCase
{
    public function testAnimeSearch()
    {
        $this->get('/v4/search/anime?order_by=id&sort=asc')
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'results' => [
                    [
                        'mal_id',
                        'url',
                        'image_url',
                        'title',
                        'airing',
                        'synopsis',
                        'type',
                        'episodes',
                        'score',
                        'start_date',
                        'end_date',
                        'members',
                        'rated'
                    ]
                ]
            ])
        ;
    }

    public function testMangaSearch()
    {
        $this->get('/v4/search/manga?order_by=id&sort=asc')
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'results' => [
                    [
                        'mal_id',
                        'url',
                        'image_url',
                        'title',
                        'publishing',
                        'synopsis',
                        'type',
                        'chapters',
                        'volumes',
                        'score',
                        'start_date',
                        'end_date',
                        'members',
                    ]
                ]
            ])
        ;
    }

    public function testPeopleSearch()
    {
        $this->get('/v4/search/people?q=Sawano')
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'results' => [
                    [
                        'mal_id',
                        'url',
                        'image_url',
                        'name',
                        'alternative_names',
                    ]
                ]
            ])
        ;
    }

    public function testCharacterSearch()
    {
        $this->get('/v4/search/character?q=Okabe,%20Rintarou')
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'results' => [
                    [
                        'mal_id',
                        'url',
                        'image_url',
                        'name',
                        'alternative_names',
                        'anime' => [
                            [
                                'mal_id',
                                'type',
                                'name', // todo should be `title`
                                'url'
                            ]
                        ],
                        'manga' => [],
                    ]
                ],
                'last_page'
            ])
        ;
    }
}
