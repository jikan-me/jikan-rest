<?php

namespace App\Contracts;

use App\Person;
use Illuminate\Contracts\Database\Query\Builder as EloquentBuilder;
use Laravel\Scout\Builder as ScoutBuilder;

/**
 * @extends Repository<Person>
 */
interface PeopleRepository extends Repository
{
    /**
     * @return \Illuminate\Contracts\Database\Query\Builder|\Laravel\Scout\Builder<\App\Person>
     */
    public function topPeople(): EloquentBuilder|ScoutBuilder;
}
