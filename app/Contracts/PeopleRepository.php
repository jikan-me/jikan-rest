<?php

namespace App\Contracts;

use App\Person;
use Illuminate\Contracts\Database\Query\Builder as EloquentBuilder;
use Laravel\Scout\Builder;
use Laravel\Scout\Builder as ScoutBuilder;

/**
 * @extends Repository<Person>
 */
interface PeopleRepository extends Repository
{
    /**
     * @return EloquentBuilder|Builder<Person>
     */
    public function topPeople(): EloquentBuilder|ScoutBuilder;
}
