<?php

namespace App\Repositories;

use App\Club;
use App\Contracts\ClubRepository;
use App\Contracts\Repository;

/**
 * @implements Repository<Club>
 */
final class DefaultClubRepository extends DatabaseRepository implements ClubRepository
{
    public function __construct()
    {
        parent::__construct(fn () => Club::query(), fn ($x, $y) => Club::search($x, $y));
    }
}
