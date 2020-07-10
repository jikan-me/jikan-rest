<?php

namespace App\Http\Controllers\V4DB;

use Jikan\Helper\Constants;
use Jikan\Request\Reviews\RecentReviewsRequest;
use Laravel\Lumen\Http\Request;

class ReviewsController extends Controller
{

    public function bestVoted(Request $request)
    {
        $page = $request->get('page') ?? 1;

        $results = $this->jikan->getRecentReviews(
            new RecentReviewsRequest(Constants::RECENT_REVIEW_BEST_VOTED, $page)
        );

        return response($this->serializer->serialize($results, 'json'));
    }

    public function anime(Request $request)
    {
        $page = $request->get('page') ?? 1;

        $results = $this->jikan->getRecentReviews(
            new RecentReviewsRequest(Constants::RECENT_REVIEW_ANIME, $page)
        );

        return response($this->serializer->serialize($results, 'json'));
    }

    public function manga(Request $request)
    {
        $page = $request->get('page') ?? 1;

        $results = $this->jikan->getRecentReviews(
            new RecentReviewsRequest(Constants::RECENT_REVIEW_MANGA, $page)
        );

        return response($this->serializer->serialize($results, 'json'));
    }
}
