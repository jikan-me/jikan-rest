<?php

namespace App\Repositories;

use App\Contracts\ProducerRepository;
use App\Contracts\Repository;
use App\Producers;

/**
 * @implements Repository<Producers>
 */
class DefaultProducerRepository extends DatabaseRepository implements ProducerRepository
{
    public function __construct()
    {
        parent::__construct(fn () => Producers::query(), fn ($x, $y) => Producers::search($x, $y));
    }
}
