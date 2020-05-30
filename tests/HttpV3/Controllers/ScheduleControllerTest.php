<?php

class ScheduleControllerTest extends TestCase
{
    public function testSchedule()
    {
        $this->get('/v3/schedule')
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'monday' => [
                    [
                        'mal_id',
                        'url',
                        'title',
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
                ],
                'tuesday',
                'wednesday',
                'thursday',
                'friday',
                'saturday',
                'sunday',
                'other',
                'unknown'
            ]);
    }

    public function test400()
    {
        $this->get('/v3/schedule/asdjkhas')
            ->seeStatusCode(400);
    }
}
