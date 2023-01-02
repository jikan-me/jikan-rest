<?php

namespace App\Repositories;

use App\Contracts\PeopleRepository;
use App\Contracts\Repository;
use App\Person;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Laravel\Scout\Builder as ScoutBuilder;

/**
 * @implements Repository<Person>
 */
class DefaultPeopleRepository extends DatabaseRepository implements PeopleRepository
{
    public function __construct()
    {
        parent::__construct(fn () => Person::query(), fn ($x, $y) => Person::search($x, $y));
    }

    public function topPeople(): EloquentBuilder|ScoutBuilder
    {
        return $this->queryable()->whereNotNull("member_favorites")
            ->where("member_favorites", ">", 0)
            ->orderBy("member_favorites", "desc");
    }
}
