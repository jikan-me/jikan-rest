<?php

class ProducerControllerTest extends TestCase
{

    public function testProducersListing()
    {
        $this->get('/v4/producers')
            ->seeStatusCode(200)
            ->seeJsonStructure(['data'=>[
                [
                    'mal_id',
                    'name',
                    'url',
                    'count'
                ]
            ]]);
    }

}
