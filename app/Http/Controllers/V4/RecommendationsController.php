<?php

namespace App\Http\Controllers\V4;

use Jikan\Helper\Constants;
use Jikan\Request\Recommendations\RecentRecommendationsRequest;

class RecommendationsController extends Controller
{

    public function anime()
    {
        $page = $_GET['page'] ?? 1;
        $results = [
            'recommendations' => $this->jikan->getRecentRecommendations(
                new RecentRecommendationsRequest(Constants::RECENT_RECOMMENDATION_ANIME, $page)
            )
        ];

        return response($this->serializer->serialize($results, 'json'));
    }

    public function manga()
    {
        $page = $_GET['page'] ?? 1;
        $results = [
            'recommendations' => $this->jikan->getRecentRecommendations(
                new RecentRecommendationsRequest(Constants::RECENT_RECOMMENDATION_MANGA, $page)
            )
        ];

        return response($this->serializer->serialize($results, 'json'));
    }
}
