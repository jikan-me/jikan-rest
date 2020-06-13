<?php

namespace App\Http\Controllers\V4DB;

use Jikan\Request\Club\ClubRequest;
use Jikan\Request\Club\UserListRequest;

class ClubController extends Controller
{
    public function main(int $id)
    {
        $club = $this->jikan->getClub(new ClubRequest($id));
        return response($this->serializer->serialize($club, 'json'));
    }

    public function members(int $id, int $page = 1)
    {
        $club = ['members' => $this->jikan->getClubUsers(new UserListRequest($id, $page))];
        return response($this->serializer->serialize($club, 'json'));
    }
}
