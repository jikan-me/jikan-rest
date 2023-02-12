<?php /** @noinspection PhpIllegalPsrClassPathInspection */
namespace Tests\Integration;
use App\GenreAnime;
use App\GenreManga;
use App\Testing\ScoutFlush;
use App\Testing\SyntheticMongoDbTransaction;
use Tests\TestCase;

class GenreControllerTest extends TestCase
{
    use SyntheticMongoDbTransaction;
    use ScoutFlush;

    public function testAnimeGenre()
    {
        GenreAnime::factory()->createOne();
        $this->get('/v4/genres/anime')
            ->seeStatusCode(200)
            ->seeJsonStructure(['data'=>[
                [
                    'mal_id',
                    'name',
                    'url',
                    'count'
                ]
            ]]);
    }

    public function testMangaGenre()
    {
        GenreManga::factory()->createOne();
        $this->get('/v4/genres/manga')
            ->seeStatusCode(200)
            ->seeJsonStructure(['data'=>[
                [
                    'mal_id',
                    'name',
                    'url',
                    'count'
                ]
            ]]);
    }

    public function test404()
    {
        $this->mockJikanParserWith404RespondingUpstream();
        $this->get('/v4/genres')
            ->seeStatusCode(404);
    }
}
