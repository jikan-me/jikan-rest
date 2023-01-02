<?php

namespace App\Contracts;

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
}
