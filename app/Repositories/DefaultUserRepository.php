<?php

namespace App\Repositories;

use App\Contracts\Repository;
use App\Contracts\UserRepository;
use App\Profile;

/**
 * @implements Repository<Profile>
 */
class DefaultUserRepository extends DatabaseRepository implements UserRepository
{
    public function __construct()
    {
        parent::__construct(fn () => Profile::query(), fn ($x, $y) => Profile::search($x, $y));
    }
}
