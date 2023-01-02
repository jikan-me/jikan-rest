<?php

namespace App\Contracts;

use App\Person;
use \Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use \Laravel\Scout\Builder as ScoutBuilder;

/**
 * @implements Repository<Person>
 */
interface PeopleRepository extends Repository
{
    public function topPeople(): EloquentBuilder|ScoutBuilder;
}
