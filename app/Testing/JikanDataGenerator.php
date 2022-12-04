<?php
namespace App\Testing;
use Faker\Generator;
use Illuminate\Support\Carbon;

trait JikanDataGenerator
{
    /**
     * The current Faker instance.
     *
     * @var Generator
     */
    protected $faker;
    private array $dummyGenres = [
        "Action",
        "Adventure",
        "Avant Garde",
        "Award Winning",
        "Boys Love",
        "Comedy",
        "Drama",
        "Fantasy",
        "Girls Love",
        "Gourmet",
        "Horror",
        "Mystery",
        "Romance",
        "Sci-Fi",
        "Slice of Life",
        "Sports",
        "Supernatural",
        "Suspense",
        "Ecchi",
        "Erotica",
        "Hentai",
        "Adult Cast",
        "Anthropomorphic",
        "CGDCT",
        "Childcare",
        "Combat Sports",
        "Crossdressing",
        "Delinquents",
        "Detective",
        "Educational",
        "Gag Humor",
        "Gore",
        "Harem",
        "High Stakes Game",
        "Historical",
        "Idols (Female)",
        "Idols (Male)",
        "Isekai",
        "Iyashikei",
        "Love Polygon",
        "Magical Sex Shift",
        "Mahou Shoujo",
        "Martial Arts",
        "Mecha",
        "Medical",
        "Military",
        "Music",
        "Mythology",
        "Organized Crime",
        "Otaku Culture",
        "Parody",
        "Performing Arts",
        "Pets",
        "Psychological",
        "Racing",
        "Reincarnation",
        "Reverse Harem",
        "Romantic Subtext",
        "Samurai",
        "School",
        "Showbiz",
        "Space",
        "Strategy Game",
        "Super Power",
        "Survival",
        "Team Sports",
        "Time Travel",
        "Vampire",
        "Video Game",
        "Visual Arts",
        "Workplace",
        "Josei",
        "Kids",
        "Seinen",
        "Shoujo",
        "Shounen"
    ];

    private function createUrl($malId, $type): string
    {
        return "https://myanimelist.net/" . $type . "/" . $malId . "/x";
    }

    private function createMalId(): int
    {
        return $this->faker->numberBetween(1, 99999);
    }

    private function createTitle(): string
    {
        return $this->faker->name();
    }

    private function createRandomDateTime($startDate = "-30 years"): Carbon
    {
        return Carbon::createFromTimestamp($this->faker->dateTimeBetween($startDate)->getTimestamp());
    }

    private function createActiveDateRange($status, $activeStatus): array
    {
        $from = $this->createRandomDateTime("-15 years");
        $to = $status != $activeStatus ? $from->addDays($this->faker->numberBetween(1, 368))->toAtomString() : null;
        return [$from, $to];
    }

    private function getRandomGenreName(): string
    {
        return $this->faker->randomElement($this->dummyGenres);
    }

    private function getRandomGenreNames($count = 1): array
    {
        return $this->faker->randomElements($this->dummyGenres, $count);
    }

    public function stuff()
    {
        return "";
    }
}
