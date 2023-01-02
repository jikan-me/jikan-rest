<?php

namespace App\Repositories;

use App\Contracts\MagazineRepository;
use App\Contracts\Repository;
use App\Magazine;

/**
 * @implements Repository<Magazine>
 */
class DefaultMagazineRepository extends DatabaseRepository implements MagazineRepository
{
    public function __construct()
    {
        parent::__construct(fn () => Magazine::query(), fn ($x, $y) => Magazine::search($x, $y));
    }
}
