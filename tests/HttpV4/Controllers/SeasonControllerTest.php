<?php

class SeasonControllerTest extends TestCase
{
    public function testSchedule()
    {
        $this->get('/v3/season')
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'season_name',
                'season_year',
                'anime' => [
                    [
                        'mal_id',
                        'url',
                        'title',
                        'image_url',
                        'synopsis',
                        'type',
                        'airing_start',
                        'episodes',
                        'members',
                        'genres' => [
                            [
                                'mal_id',
                                'type',
                                'name',
                                'url'
                            ]
                        ],
                        'source',
                        'producers' => [
                            [
                                'mal_id',
                                'type',
                                'name',
                                'url'
                            ]
                        ],
                        'score',
                        'licensors',
                        'r18',
                        'kids',
                        'continuing'
                    ]
                ]
            ]);
    }
}
