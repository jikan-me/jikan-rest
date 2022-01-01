<?php

namespace App\Http\Controllers\V4;

use Jikan\Request\Producer\ProducerRequest;
use Jikan\Request\Producer\ProducersRequest;

class ProducerController extends Controller
{

    public function main()
    {
        $results = $this->jikan->getProducers(new ProducersRequest());
        return response($this->serializer->serialize($results, 'json'));
    }

    public function resource(int $id, int $page = 1)
    {
        $producer = $this->jikan->getProducer(new ProducerRequest($id, $page));
        return response($this->serializer->serialize($producer, 'json'));
    }
}
