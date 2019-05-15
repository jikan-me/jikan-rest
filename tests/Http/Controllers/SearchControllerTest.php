<?php

class SearchControllerTest extends TestCase
{
    public function testSearch()
    {
        $this->get('/v3/search/anime?order_by=9&sort=asc')
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
            ->seeJson([
                'request_cached' => true
            ])
        ;
    }
}
