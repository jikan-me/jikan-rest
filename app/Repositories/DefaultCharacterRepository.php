<?php

namespace App\Repositories;

use App\Character;
use App\Contracts\CharacterRepository;
use Illuminate\Contracts\Database\Query\Builder as EloquentBuilder;
use Laravel\Scout\Builder;
use Laravel\Scout\Builder as ScoutBuilder;

final class DefaultCharacterRepository extends DatabaseRepository implements CharacterRepository
{
    public function __construct()
    {
        parent::__construct(fn () => Character::query(), fn ($x, $y) => Character::search($x, $y));
    }

    /**
     * @return EloquentBuilder|Builder<Character>
     */
    public function topCharacters(): EloquentBuilder|ScoutBuilder
    {
        return $this->queryable()->whereNotNull('member_favorites')
            ->where('member_favorites', '>', 0)
            ->orderBy('member_favorites', 'desc');
    }
}
