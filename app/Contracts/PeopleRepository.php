<?php

namespace App\Contracts;

use App\Person;
use Illuminate\Contracts\Database\Query\Builder as EloquentBuilder;
use Laravel\Scout\Builder as ScoutBuilder;

/**
 * @implements Repository<Person>
 */
interface PeopleRepository extends Repository
{
    public function topPeople(): EloquentBuilder|ScoutBuilder;
}
