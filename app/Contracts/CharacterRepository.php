<?php

namespace App\Contracts;

use App\Character;
use Illuminate\Contracts\Database\Query\Builder as EloquentBuilder;
use Laravel\Scout\Builder as ScoutBuilder;

/**
 * @implements Repository<Character>
 */
interface CharacterRepository extends Repository
{
    public function topCharacters(): EloquentBuilder|ScoutBuilder;
}
