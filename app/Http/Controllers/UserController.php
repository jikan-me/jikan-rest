<?php

namespace App\Http\Controllers;

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

        $person = $this->jikan->getUserHistory(new UserHistoryRequest($username, $type));

        return response($this->serializer->serialize($person, 'json'));
    }

    public function friends(string $username, int $page = 1)
    {
        $person = $this->jikan->getUserFriends(new UserFriendsRequest($username, $page));
        return response($this->serializer->serialize($person, 'json'));
    }
}
