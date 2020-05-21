<?php

namespace App\Http\Controllers\V4;

use Jikan\Request\User\RecentlyOnlineUsersRequest;
use Jikan\Request\User\UserAnimeListRequest;
use Jikan\Request\User\UserClubsRequest;
use Jikan\Request\User\UserMangaListRequest;
use Jikan\Request\User\UserProfileRequest;
use Jikan\Request\User\UserFriendsRequest;
use Jikan\Request\User\UserHistoryRequest;
use Jikan\Request\User\UserRecommendationsRequest;
use Jikan\Request\User\UserReviewsRequest;

class UserController extends Controller
{
    public function profile(string $username)
    {
        $user = $this->jikan->getUserProfile(new UserProfileRequest($username));
        return response($this->serializer->serialize($user, 'json'));
    }

    public function history(string $username, ?string $type = null)
    {
        if (!is_null($type) && !\in_array(strtolower($type), ['anime', 'manga'])) {
            return response()->json([
                'error' => 'Bad Request'
            ])->setStatusCode(400);
        }

        $person = ['history' => $this->jikan->getUserHistory(new UserHistoryRequest($username, $type))];

        return response($this->serializer->serialize($person, 'json'));
    }

    public function friends(string $username, int $page = 1)
    {
        $person = ['friends' => $this->jikan->getUserFriends(new UserFriendsRequest($username, $page))];
        return response($this->serializer->serialize($person, 'json'));
    }

    public function animelist(string $username, ?string $status = null, int $page = 1)
    {
        if (!is_null($status)) {
            $status = strtolower($status);

            if (!\in_array($status, ['all', 'watching', 'completed', 'onhold', 'dropped', 'plantowatch', 'ptw'])) {
                return response()->json([
                    'error' => 'Bad Request'
                ])->setStatusCode(400);
            }
        }
        $status = $this->listStatusToId($status);

        return response(
            $this->serializer->serialize(
                [
                    'anime' => $this->jikan->getUserAnimeList(
                        new UserAnimeListRequest($username, $page, $status)
                    )
                ],
                'json'
            )
        );
    }

    public function mangalist(string $username, ?string $status = null, int $page = 1)
    {
        if (!is_null($status)) {
            $status = strtolower($status);

            if (!\in_array($status, ['all', 'reading', 'completed', 'onhold', 'dropped', 'plantoread', 'ptr'])) {
                return response()->json([
                    'error' => 'Bad Request'
                ])->setStatusCode(400);
            }
        }
        $status = $this->listStatusToId($status);

        return response(
            $this->serializer->serialize(
                [
                    'manga' => $this->jikan->getUserMangaList(
                        new UserMangaListRequest($username, $page, $status)
                    )
                ],
                'json'
            )
        );
    }

    public function reviews(string $username)
    {
        $page = $_GET['page'] ?? 1;
        $results = $this->jikan->getUserReviews(
            new UserReviewsRequest($username, $page)
        );

        return response($this->serializer->serialize($results, 'json'));
    }

    public function recommendations(string $username)
    {
        $page = $_GET['page'] ?? 1;
        $results = $this->jikan->getUserRecommendations(
            new UserRecommendationsRequest($username, $page)
        );

        return response($this->serializer->serialize($results, 'json'));
    }

    public function clubs(string $username)
    {
        $results = [
            'clubs' => $this->jikan->getUserClubs(
                new UserClubsRequest($username)
            )
        ];

        return response($this->serializer->serialize($results, 'json'));
    }

    public function recentlyOnline()
    {
        $results = [
            'users' => $this->jikan->getRecentOnlineUsers(
                new RecentlyOnlineUsersRequest()
            )
        ];

        return response($this->serializer->serialize($results, 'json'));
    }

    private function listStatusToId(?string $status) : int
    {
        if (is_null($status)) {
            return 7;
        }

        switch ($status) {
            case 'all':
                return 7;
            case 'watching':
            case 'reading':
                return 1;
            case 'completed':
                return 2;
            case 'onhold':
                return 3;
            case 'dropped':
                return 4;
            case 'plantowatch':
            case 'ptw':
            case 'plantoread':
            case 'ptr':
                return 6;
            default:
                return 7;
        }
    }
}
