<?php

namespace App\Support;

use App\Contracts\AnimeRepository;
use App\Contracts\CharacterRepository;
use App\Contracts\ClubRepository;
use App\Contracts\GenreRepository;
use App\Contracts\MagazineRepository;
use App\Contracts\MangaRepository;
use App\Contracts\PeopleRepository;
use App\Contracts\ProducerRepository;
use App\Contracts\Repository;
use App\Contracts\UnitOfWork;
use App\Contracts\UserRepository;
use App\Repositories\AnimeGenresRepository;
use App\Repositories\DatabaseRepository;
use App\Repositories\DocumentRepository;
use App\Repositories\MangaGenresRepository;

final class JikanUnitOfWork implements UnitOfWork
{
    public function __construct(
        private readonly AnimeRepository $animeRepository,
        private readonly MangaRepository $mangaRepository,
        private readonly CharacterRepository $characterRepository,
        private readonly PeopleRepository $peopleRepository,
        private readonly ClubRepository $clubRepository,
        private readonly ProducerRepository $producerRepository,
        private readonly MagazineRepository $magazineRepository,
        private readonly UserRepository $userRepository,
        private readonly AnimeGenresRepository $animeGenresRepository,
        private readonly MangaGenresRepository $mangaGenresRepository
    )
    {
    }

    public function anime(): AnimeRepository
    {
        return $this->animeRepository;
    }

    public function manga(): MangaRepository
    {
        return $this->mangaRepository;
    }

    public function characters(): CharacterRepository
    {
        return $this->characterRepository;
    }

    public function people(): PeopleRepository
    {
        return $this->peopleRepository;
    }

    public function clubs(): ClubRepository
    {
        return $this->clubRepository;
    }

    public function producers(): ProducerRepository
    {
        return $this->producerRepository;
    }

    public function magazines(): MagazineRepository
    {
        return $this->magazineRepository;
    }

    public function users(): UserRepository
    {
        return $this->userRepository;
    }

    public function animeGenres(): GenreRepository
    {
        return $this->animeGenresRepository;
    }

    public function mangaGenres(): GenreRepository
    {
        return $this->mangaGenresRepository;
    }

    /**
     * @param class-string $modelClass
     * @return Repository
     * @noinspection PhpUndefinedMethodInspection
     */
    private function getRepository(string $modelClass): Repository
    {
        return new DatabaseRepository(fn () => $modelClass::query(), fn ($x, $y) => $modelClass::search($x, $y));
    }

    public function documents(string $tableName): DocumentRepository
    {
        return new DocumentRepository($tableName);
    }
}
