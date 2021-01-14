<?php

class SeasonControllerTest extends TestCase
{
    public function testSeasons()
    {
        $this->get('/v4/seasons')
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'data' => [
                    'year',
                    'seasons'
                ]
            ]);

        // @todo add seasons test once database is populated
    }

    public function testSchedule()
    {
        // @todo add schedules tests once database is populated
    }
}
