<?php

namespace App\Http\Controllers\V4;

use Jikan\Helper\Constants;
use Jikan\Request\Reviews\RecentReviewsRequest;

class ReviewsController extends Controller
{

    public function bestVoted()
    {
        $page = $_GET['page'] ?? 1;
        $results = $this->jikan->getRecentReviews(
            new RecentReviewsRequest(Constants::RECENT_REVIEW_BEST_VOTED, $page)
        );

        return response($this->serializer->serialize($results, 'json'));
    }

    public function anime()
    {
        $page = $_GET['page'] ?? 1;
        $results = $this->jikan->getRecentReviews(
            new RecentReviewsRequest(Constants::RECENT_REVIEW_ANIME, $page)
        );

        return response($this->serializer->serialize($results, 'json'));
    }

    public function manga()
    {
        $page = $_GET['page'] ?? 1;
        $results = $this->jikan->getRecentReviews(
            new RecentReviewsRequest(Constants::RECENT_REVIEW_MANGA, $page)
        );

        return response($this->serializer->serialize($results, 'json'));
    }
}
