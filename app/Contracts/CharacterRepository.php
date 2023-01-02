<?php

namespace App\Contracts;

use App\Character;
use \Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use \Laravel\Scout\Builder as ScoutBuilder;

/**
 * @implements Repository<Character>
 */
interface CharacterRepository extends Repository
{
    public function topCharacters(): EloquentBuilder|ScoutBuilder;
}
