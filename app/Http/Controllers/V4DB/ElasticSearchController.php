<?php

namespace App\Http\Controllers\V4DB;
use Elastic\Elasticsearch\ClientBuilder;
use Elastic\Elasticsearch\Exception\AuthenticationException;
use Illuminate\Http\Request;

class ElasticSearchController extends Controller
{

    /**
     * @throws AuthenticationException
     */
    public function anime(Request $request)
    {
        $page = $request->get('page') ?? 1;
        $limit = $request->get('limit') ?? env('MAX_RESULTS_PER_PAGE', 25);
        $q = $request->get('q') ?? '';

        if (!is_null($limit)) {
            $limit = (int) $limit;

            if ($limit <= 0)
                $limit = 1;

            if ($limit > env('MAX_RESULTS_PER_PAGE', 25))
                $limit = env('MAX_RESULTS_PER_PAGE', 25);
        }

        $client = ClientBuilder::create()
            ->setHosts([env('ELASTICSEARCH_HOST', 'localhost').":".env('ELASTICSEARCH_PORT', 9200)])
            ->setBasicAuthentication(env('ELASTICSEARCH_USER', 'elastic'), env('ELASTICSEARCH_PASS', ''))
            ->build();

//        $response = $client->search([
//            'index' => 'jikan.anime',
//            'body' => [
//                'query' => [
//                    'multi_match' => [
//                        'query' => $q,
//                        'fields' => ['title^3', 'title_english^2', 'title_japanese', 'title_synonyms']
//                    ]
//                ]
//            ]
//        ]);

        $response = $client->search([
            'index' => 'jikan.anime',
            'body' => [
                'query' => [
                    'multi_match' => [
                        'query' => $q,
                        'type' => 'most_fields',
                        'fields' => ['title^3', 'title_english^2', 'title_japanese', 'title_synonyms'],

                    ]
                ]
            ]
        ]);



        $arr = [];
        foreach ($response['hits']['hits'] as $result) {
            $arr[] = [
                'title' => $result['_source']['title'],
                'title_english' => $result['_source']['title_english'],
                'title_japanese' => $result['_source']['title_japanese'],
                'title_synonyms' => implode(", ", $result['_source']['title_synonyms']),
            ];
        }

        return response()->json($arr);
    }

}
