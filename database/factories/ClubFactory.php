<?php

namespace Database\Factories;

use App\Club;
use App\Enums\ClubCategoryEnum;
use App\Enums\ClubOrderByEnum;
use App\Enums\ClubTypeEnum;
use App\Testing\JikanDataGenerator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use MongoDB\BSON\UTCDateTime;

final class ClubFactory extends JikanModelFactory
{
    use JikanDataGenerator;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Club::class;

    protected function definitionInternal(): array
    {
        $mal_id = $this->createMalId();
        $url = "https://myanimelist.net/clubs.php?cid=".$mal_id;
        $createdAt = Carbon::createFromTimestamp(
            $this->faker->dateTime()->getTimestamp())->toAtomString();
        $modifiedAt = Carbon::createFromTimestamp(
            $this->faker->dateTime()->getTimestamp())->toDateTimeString();

        return [
            "mal_id" => $mal_id,
            "url" => $url,
            "images" => [
                "jpg" => [
                    "image_url" => "https://cdn.myanimelist.net/images/clubs/16/222057.jpg"
                ]
            ],
            "category" => $this->faker->randomElement(["anime", "manga", "characters"]),
            "created" => $createdAt,
            "createdAt" => new UTCDateTime(),
            "modifiedAt" => new UTCDateTime(),
            "created_at" => $createdAt,
            "updated_at" => $modifiedAt,
            "name" => $this->faker->name(),
            "request_hash" => sprintf("request:%s:%s", "v4", $this->getItemTestUrl("club", $mal_id)),
            "anime" => [
                [
                    "mal_id" => $this->createMalId(),
                    "type" => "anime",
                    "name" => $this->faker->name(),
                    "url" => "https://myanimelist.net/anime/1/x"
                ]
            ],
            "characters" => [
                [
                    "mal_id" => $this->createMalId(),
                    "type" => "character",
                    "name" => $this->faker->name(),
                    "url" => "https://myanimelist.net/character/1234"
                ]
            ],
            "manga" => [
                [
                    "mal_id" => $this->createMalId(),
                    "type" => "manga",
                    "name" => $this->faker->name(),
                    "url" => "https://myanimelist.net/manga/1/x"
                ]
            ],
            "staff" => [
                [
                    "url" => "https://myanimelist.net/profile/cyruz",
                    "username" => "cryuz"
                ]
            ],
            "members" => $this->faker->numberBetween(1, 9999),
            "access" => $this->faker->randomElement(["public", "private"])
        ];
    }

    public function overrideFromQueryStringParameters(array $additionalParams, bool $doOpposite = false): self
    {
        $additionalParams = collect($additionalParams);

        if ($doOpposite) {
            $overrides = $this->getOppositeOverridesFromQueryStringParameters($additionalParams);
        }
        else {
            $overrides = $this->getOverridesFromQueryStringParameters($additionalParams);
        }

        return $this->state($this->serializeStateDefinition($overrides));
    }

    protected function getOverridesFromQueryStringParameters(Collection $additionalParams): array
    {
        $overrides = [];

        if ($additionalParams->has("type")) {
            $typeOverride = collect(ClubTypeEnum::toArray())->get(strtolower($additionalParams["type"]));
            if (!is_null($typeOverride)) {
                $overrides["type"] = $typeOverride;
            }
        }

        if ($additionalParams->has("letter")) {
            $overrides["name"] = $additionalParams["letter"] . $this->createTitle();
        }

        if ($additionalParams->has("category")) {
            $categoryOverride = collect(ClubCategoryEnum::toArray())->get(strtolower($additionalParams["category"]));
            if (!is_null($categoryOverride)) {
                $overrides["category"] = $categoryOverride;
            }
        }

        return $overrides;
    }

    protected function getOppositeOverridesFromQueryStringParameters(Collection $additionalParams): array
    {
        $overrides = [];

        if ($additionalParams->has("type")) {
            // value => label  key pairs
            // we store labels in the database
            $types = ClubTypeEnum::toArray();
            $typeKey = $this->faker->randomElement(array_diff(array_keys($types), [$additionalParams["type"]]));
            $overrides["type"] = $types[$typeKey];
        }

        if ($additionalParams->has("letter")) {
            $alphabet = array_filter(range("a", "z"), fn ($elem) => $elem !== $additionalParams["letter"]);
            $overrides["name"] = $this->faker->randomElement($alphabet) . $this->createTitle();
        }

        if ($additionalParams->has("category")) {
            $categories = ClubCategoryEnum::toArray();
            $categoryKey = $this->faker->randomElement(array_diff(array_keys($categories), [$additionalParams["category"]]));
            $overrides["category"] = $categories[$categoryKey];
        }

        return $overrides;
    }

    public function createManyWithOrder(string $orderByField): Collection
    {
        $count = $this->count ?? 3;
        $items = collect();

        $fieldValueGenerator = match($orderByField) {
            ClubOrderByEnum::name()->value => ((function() {
                $alphabet = range("a", "z");
                $alphabetCount = count($alphabet);
                return fn($i) => $alphabet[$i % $alphabetCount];
            })()),
            ClubOrderByEnum::created()->value => ((function() {
                $randomDate = $this->createRandomDateTime("-5 years");
                return fn($i) => new UTCDateTime($randomDate->copy()->addDays($i)->getPreciseTimestamp(3));
            })()),
            default => fn($i) => $i,
        };

        for ($i = 1; $i <= $count; $i++) {
            $createdItem = $this->createOne([
                $orderByField => $fieldValueGenerator($i)
            ]);

            $items->add($createdItem);
        }

        return $items;
    }
}
