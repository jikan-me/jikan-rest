<?php

namespace App\Contracts;

use App\Repositories\DocumentRepository;

interface UnitOfWork
{
    public function anime(): AnimeRepository;

    public function manga(): MangaRepository;

    public function characters(): CharacterRepository;

    public function people(): PeopleRepository;

    public function clubs(): ClubRepository;

    public function producers(): ProducerRepository;

    public function magazines(): MagazineRepository;

    public function users(): UserRepository;

    public function animeGenres(): GenreRepository;

    public function mangaGenres(): GenreRepository;

    /**
     * Returns the repository instance for a document collection which doesn't have a model representation.
     * @param string $tableName
     * @return DocumentRepository
     */
    public function documents(string $tableName): DocumentRepository;
}
