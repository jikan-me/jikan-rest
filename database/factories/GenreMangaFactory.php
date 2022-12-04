<?php
namespace Database\Factories;
use App\GenreManga;

class GenreMangaFactory extends GenreFactory
{
    protected $model = GenreManga::class;
    protected string $mediaType = "manga";
}
