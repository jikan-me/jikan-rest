<?php
namespace Database\Factories;
use App\GenreAnime;

class GenreAnimeFactory extends GenreFactory
{
    protected $model = GenreAnime::class;
    protected string $mediaType = "anime";
}
