<?php

namespace App\Http\Controllers\V3;

use App\Providers\UserListQueryBuilder;
use Illuminate\Http\Request;
use Jikan\Request\User\UserAnimeListRequest;
use Jikan\Request\User\UserMangaListRequest;
use Jikan\Request\User\UserProfileRequest;
use Jikan\Request\User\UserFriendsRequest;
use Jikan\Request\User\UserHistoryRequest;

class UserController extends Controller
{
    public function profile(string $username)
    {
        $person = $this->jikan->getUserProfile(new UserProfileRequest($username));
        return response($this->serializer->serialize($person, 'json'));
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

    public function animelist(Request $request, string $username, ?string $status = null, int $page = 1)
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
                        UserListQueryBuilder::create(
                            $request,
                            (new UserAnimeListRequest($username))
                                ->setPage($page)
                                ->setStatus($status)
                        )
                    )
                ],
                'json'
            )
        );
    }

    public function mangalist(Request $request, string $username, ?string $status = null, int $page = 1)
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
                        UserListQueryBuilder::create(
                            $request,
                            (new UserMangaListRequest($username))
                                ->setPage($page)
                                ->setStatus($status)
                        )
                    )
                ],
                'json'
            )
        );
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
