<?php
namespace Database\Factories;
use App\Episode;
use App\Testing\JikanDataGenerator;
use MongoDB\BSON\UTCDateTime;

class EpisodeFactory extends JikanModelFactory
{
    use JikanDataGenerator;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Episode::class;

    protected function definitionInternal(): array
    {
        $mal_id = $this->createMalId();
        $title = $this->createTitle();
        $score = $this->faker->randomFloat(2, 1.00, 9.99);
        $aired = $this->createRandomDateTime()->toAtomString();

        return [
            "mal_id" => $mal_id,
            "url" => "https://myanimelist.net/anime/$mal_id/x/episode/$mal_id",
            "title" => $title,
            "title_japanese" => $title,
            "title_romaji" => $title,
            "aired" => $aired,
            "score" => $score,
            "filler" => false,
            "recap" => false,
            "forum_url" => "https://myanimelist.net/forum/?topicid=1919480",
            "synopsis" => "test",
            "createdAt" => new UTCDateTime(),
            "modifiedAt" => new UTCDateTime(),
            "request_hash" => "request:anime:" . sha1(env('APP_URL') . "/v4/" . "anime/" . $mal_id . "/episodes")
        ];
    }

    public function updateRequestHash(int $anime_mal_id): self
    {
        return $this->state([
            "request_hash" =>
                "request:anime:" . sha1(env('APP_URL') . "/v4/" . "anime/" . $anime_mal_id . "/episodes")
        ]);
    }
}
