<?php

namespace App\Contracts;

use App\Character;
use Illuminate\Contracts\Database\Query\Builder as EloquentBuilder;
use Laravel\Scout\Builder as ScoutBuilder;

/**
 * @extends Repository<Character>
 */
interface CharacterRepository extends Repository
{
    /**
     * @return \Illuminate\Contracts\Database\Query\Builder|\Laravel\Scout\Builder<\App\Character>
     */
    public function topCharacters(): EloquentBuilder|ScoutBuilder;
}
