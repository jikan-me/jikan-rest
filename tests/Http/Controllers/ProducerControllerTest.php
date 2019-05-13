<?php

class ProducerControllerTest extends TestCase
{
    public function testProducer()
    {
        $this->get('/v3/producer/1')
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'meta' => [
                    'mal_id',
                    'type',
                    'name',
                    'url'
                ],
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
                        'kids'
                    ]
                ]
            ]);
    }

    public function test404()
    {
        $this->get('/v3/producer/1/1000')
            ->seeStatusCode(404);
        $this->get('/v3/producer/1/1000')
            ->seeStatusCode(404);
        $this->get('/v3/producer/100000')
            ->seeStatusCode(404);
        $this->get('/v3/producer/100000')
            ->seeStatusCode(404);
    }
}
