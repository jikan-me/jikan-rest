<?php

namespace Database\Factories;

use App\CarbonDateRange;
use App\Http\QueryBuilder\AnimeSearchQueryBuilder;
use App\Testing\JikanDataGenerator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

abstract class JikanMediaModelFactory extends JikanModelFactory implements MediaModelFactory
{
    use JikanDataGenerator;

    protected ?MediaModelFactoryDescriptor $descriptor;

    public function __construct(
        MediaModelFactoryDescriptor $descriptor,
        ?int        $count = null,
        ?Collection $states = null,
        ?Collection $has = null,
        ?Collection $for = null,
        ?Collection $afterMaking = null,
        ?Collection $afterCreating = null,
                    $connection = null,
        ?Collection $recycle = null)
    {
        parent::__construct($count, $states, $has, $for, $afterMaking, $afterCreating, $connection, $recycle);
        $this->descriptor = $descriptor;
    }

    protected function newInstance(array $arguments = []): self
    {
        return App::makeWith(static::class, array_merge([
            'count' => $this->count,
            'states' => $this->states,
            'has' => $this->has,
            'for' => $this->for,
            'afterMaking' => $this->afterMaking,
            'afterCreating' => $this->afterCreating,
            'connection' => $this->connection,
            'recycle' => $this->recycle,
        ], $arguments));
    }

    /**
     * Helper function for overriding fields of the model factory based on query string parameters.
     *
     * @param array $additionalParams
     * @param bool $doOpposite
     * @return self
     */
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

    /**
     * Helper function to create records in db in sequential order.
     * The records are created with an increasing value for the specified field.
     * @param string $orderByField The field to order items by. Used to generate increasing value for the field.
     * @return Collection The created items/records
     */
    public function createManyWithOrder(string $orderByField): Collection
    {
        $count = $this->count ?? 3;
        $items = collect();

        $activityMarkerKeyName = $this->descriptor->activityMarkerKeyName();
        $fromActivityMarkerKeyPath = "$activityMarkerKeyName.from";
        $toActivityMarkerKeyPath = "$activityMarkerKeyName.to";

        $fieldValueGenerator = match($orderByField) {
            $fromActivityMarkerKeyPath, $toActivityMarkerKeyPath => ((function() {
                $randomDate = $this->createRandomDateTime("-5 years");
                return fn($i) => $randomDate->copy()->addDays($i);
            })()),
            "rating" => ((function() {
                $validRatingItems = array_values(AnimeSearchQueryBuilder::MAP_RATING);
                $validRatingItemsCount = count($validRatingItems);
                return fn($i) => $validRatingItems[$i % $validRatingItemsCount];
            })()),
            "title" => ((function() {
                $alphabet = range("a", "z");
                $alphabetCount = count($alphabet);
                return fn($i) => $alphabet[$i % $alphabetCount];
            })()),
            "type" => ((function() {
                $types = array_values(AnimeSearchQueryBuilder::MAP_TYPES);
                $typesCount = count($types);
                return fn($i) => $types[$i % $typesCount];
            })()),
            default => fn($i) => $i,
        };

        for ($i = 1; $i <= $count; $i++) {
            if ($orderByField === $fromActivityMarkerKeyPath) {
                $createdItem = $this->createOne($this->serializeStateDefinition([
                    $activityMarkerKeyName => new CarbonDateRange($fieldValueGenerator($i), null)
                ]));
            } else if ($orderByField === $toActivityMarkerKeyPath) {
                $createdItem = $this->createOne($this->serializeStateDefinition([
                    $activityMarkerKeyName => new CarbonDateRange(null, $fieldValueGenerator($i))
                ]));
            } else {
                $createdItem = $this->createOne([
                    $orderByField => $fieldValueGenerator($i)
                ]);
            }
            $items->add($createdItem);
        }

        return $items;
    }

    protected function getOverridesFromQueryStringParameters(Collection $additionalParams): array
    {
        $overrides = [];
        $activityMarkerKeyName = $this->descriptor->activityMarkerKeyName();
        // let's make all database items the same type
        if ($additionalParams->has("type")) {
            $typeOverride = collect($this->descriptor->typeParamMap())->get(strtolower($additionalParams["type"]));
            if (!is_null($typeOverride)) {
                $overrides["type"] = $typeOverride;
            }
        }

        if ($additionalParams->has("letter")) {
            $title = $additionalParams["letter"] . $this->createTitle();
            $a = [
                "titles" => [
                    [
                        "type" => "Default",
                        "title" => $title
                    ]
                ],
                "title" => $title,
                "title_english" => $title,
                "title_japanese" => $title,
                "title_synonyms" => [$title],
            ];
            $overrides = [...$overrides, ...$a];
        }

        if ($additionalParams->has("min_score") && !$additionalParams->has("max_score")) {
            $min_score = floatval($additionalParams["min_score"]);
            if ($this->isScoreValueValid($min_score)) {
                $overrides["score"] = $this->faker->randomFloat(2, floatval($additionalParams["min_score"]), 9.99);
            }
        }

        if (!$additionalParams->has("min_score") && $additionalParams->has("max_score")) {
            $max_score = floatval($additionalParams["max_score"]);

            if ($this->isScoreValueValid($max_score)) {
                $overrides["score"] = $this->faker->randomFloat(2, 1.00, floatval($additionalParams["max_score"]));
            }
        }

        if ($additionalParams->has(["min_score", "max_score"])) {
            $min_score = floatval($additionalParams["min_score"]);
            $max_score = floatval($additionalParams["max_score"]);

            if ($this->isScoreValueValid($min_score) && $this->isScoreValueValid($max_score)) {
                $overrides["score"] = $this->faker->randomFloat(2, floatval($additionalParams["min_score"]), floatval($additionalParams["max_score"]));
            }
        }

        if ($additionalParams->has("status")) {
            $statusParamMap = $this->descriptor->statusParamMap();
            $statusOverride = collect($statusParamMap)->get(strtolower($additionalParams["status"]));
            if (!is_null($statusOverride)) {
                $overrides["status"] = $statusOverride;
            }
            else {
                $overrides["status"] = $this->faker->randomElement(array_values($statusParamMap));
            }

            $logicalActivityMarker = $this->descriptor->activityMarkerLogicalKeyName();

            if (Str::contains(strtolower($overrides["status"]), strtolower($this->descriptor->statusParamMap()[$logicalActivityMarker]))) {
                $overrides[$logicalActivityMarker] = true;
            }
            else {
                $overrides[$logicalActivityMarker] = false;
            }
        }

        if ($this->descriptor->hasRatingParam() && $additionalParams->has("rating")) {
            $overrides["rating"] = match (strtolower($additionalParams["rating"])) {
                "g" => "G - All Ages",
                "pg" => "PG - Children",
                "pg13" => "PG-13 - Teens 13 or older",
                "r17" => "R - 17+ (violence & profanity)",
                "r" => "R+ - Mild Nudity",
                "rx" => "Rx - Hentai",
                default => $this->getRandomRating()
            };
        }

        if ($additionalParams->has("genres")) {
            $overrides["genres"] = [];
            $genreIds = explode(",", $additionalParams["genres"]);
            $genreIds = $this->faker->randomElements($genreIds, $this->faker->numberBetween(0, count($genreIds)));
            foreach ($genreIds as $genreId) {
                $m = $this->ensureGenreExists($genreId);

                $overrides["genres"][] = [
                    "mal_id" => $m->mal_id,
                    "type" => $this->descriptor->mediaName(),
                    "name" => $m->name,
                    "url" => $m->url
                ];
            }
        }

        if ($additionalParams->has("start_date") && !empty($additionalParams["start_date"])
            && !$additionalParams->has("end_date")) {
            $startDate = $this->adaptDateString($additionalParams["start_date"]);
            $dt = Carbon::parse($startDate)->addDays($this->faker->numberBetween(0, 25));
            $overrides[$activityMarkerKeyName] = new CarbonDateRange($dt, null);
        }

        if ($additionalParams->has("end_date") && !empty($additionalParams["end_date"])
            && !$additionalParams->has("start_date")) {
            $endDate = $this->adaptDateString($additionalParams["end_date"]);
            $to = Carbon::parse($endDate);
            $from = $to->copy()->subDays($this->faker->randomElement([30, 60, 90, 120, 180]));
            $overrides[$activityMarkerKeyName] = new CarbonDateRange($from, $to->subDays($this->faker->numberBetween(0, 25)));
        }

        if ($additionalParams->has(["start_date", "end_date"])
            && !empty($additionalParams["start_date"]) && !empty($additionalParams["end_date"])) {
            $startDate = $this->adaptDateString($additionalParams["start_date"]);
            $from = Carbon::parse($startDate);
            $endDate = $this->adaptDateString($additionalParams["end_date"]);
            $to = Carbon::parse($endDate);

            $overrides[$activityMarkerKeyName] = new CarbonDateRange($from, $to);
        }

        return $overrides;
    }

    protected function getOppositeOverridesFromQueryStringParameters(Collection $additionalParams): array
    {
        $overrides = [];
        $activityMarkerKeyName = $this->descriptor->activityMarkerKeyName();

        if ($additionalParams->has("type")) {
            $types = $this->descriptor->typeParamMap();
            $overrides["type"] = $this->faker->randomElement(array_diff(array_keys($types), [$additionalParams["type"]]));
        }

        if ($additionalParams->has("letter")) {
            $alphabet = array_filter(range("a", "z"), fn ($elem) => $elem !== $additionalParams["letter"]);
            $title = $this->faker->randomElement($alphabet) . $this->createTitle();
            $a = [
                "titles" => [
                    [
                        "type" => "Default",
                        "title" => $title
                    ]
                ],
                "title" => $title,
                "title_english" => $title,
                "title_japanese" => $title,
                "title_synonyms" => [$title],
            ];
            $overrides = [...$overrides, ...$a];
        }

        if ($additionalParams->has("min_score") && !$additionalParams->has("max_score")) {
            $min_score = floatval($additionalParams["min_score"]);
            if ($this->isScoreValueValid($min_score)) {
                $overrides["score"] = $this->faker->randomFloat(2, 1.00, floatval($additionalParams["min_score"]));
            }
        }

        if (!$additionalParams->has("min_score") && $additionalParams->has("max_score")) {
            $max_score = $additionalParams["max_score"];
            if ($this->isScoreValueValid($max_score)) {
                $overrides["score"] = $this->faker->randomFloat(2, floatval($additionalParams["max_score"]), 9.99);
            }
        }

        if ($additionalParams->has("min_score") && $additionalParams->has("max_score")) {
            $min_score = floatval($additionalParams["min_score"]);
            $max_score = floatval($additionalParams["max_score"]);

            if ($this->isScoreValueValid($min_score) && $this->isScoreValueValid($max_score))
            {
                $overrides["score"] = $this->faker->randomElement([
                    $this->faker->randomFloat(2, 1.00, floatval($additionalParams["min_score"])),
                    $this->faker->randomFloat(2, floatval($additionalParams["max_score"]), 9.99)
                ]);
            }
        }

        if ($additionalParams->has("status")) {
            $statuses = $this->descriptor->statusParamMap();

            $rndKey = $this->faker->randomElement(array_diff(array_keys($statuses), [strtolower($additionalParams["status"])]));
            $overrides["status"] = $statuses[$rndKey];

            $logicalActivityMarker = $this->descriptor->activityMarkerLogicalKeyName();

            if (Str::contains(strtolower($overrides["status"]), strtolower($this->descriptor->statusParamMap()[$logicalActivityMarker]))) {
                $overrides[$logicalActivityMarker] = true;
            }
            else {
                $overrides[$logicalActivityMarker] = false;
            }
        }

        if ($this->descriptor->hasRatingParam() && $additionalParams->has("rating")) {
            $ratings = [
                "g" => "G - All Ages",
                "pg" => "PG - Children",
                "pg13" => "PG-13 - Teens 13 or older",
                "r17" => "R - 17+ (violence & profanity)",
                "r" => "R+ - Mild Nudity",
                "rx" => "Rx - Hentai",
            ];
            $rndKey = $this->faker->randomElement(array_diff(array_keys($ratings), [strtolower($additionalParams["rating"])]));
            $overrides["rating"] = $ratings[$rndKey];
        }

        if (($additionalParams->has("genres") && $additionalParams->has("genres_exclude")) || (
                !$additionalParams->has("genres") && $additionalParams->has("genres_exclude")
            ) ) {
            $overrides["genres"] = [];
            // use the "genres_exclude" values to add genres to the anime item
            $genreIds = explode(",", $additionalParams["genres_exclude"]);
            if (count($genreIds) > 1) {
                $genreId = $this->faker->randomElement($genreIds);
            } else {
                $genreId = $genreIds[0];
            }

            $m = $this->ensureGenreExists($genreId);
            $overrides["genres"][] = [
                "mal_id" => $m->mal_id,
                "type" => $this->descriptor->mediaName(),
                "name" => $m->name,
                "url" => $m->url
            ];
        } else if ($additionalParams->has("genres")) {
            $overrides["genres"] = [];
            // add such genres which are not in the "genres" param
            $genreIds = explode(",", $additionalParams["genres"]);
            $numberOfGenresToAdd = $this->faker->numberBetween(0, 4);
            for ($numberOfGenresAdded = 0; $numberOfGenresAdded <= $numberOfGenresToAdd; $numberOfGenresAdded++) {
                $outboundsGenreId = $this->faker->numberBetween(0, 74);
                while (in_array($outboundsGenreId, $genreIds)) {
                    $outboundsGenreId = $this->faker->numberBetween(0, 74);
                }

                $m = $this->ensureGenreExists($outboundsGenreId);

                $overrides["genres"][] = [
                    "mal_id" => $m->mal_id,
                    "type" => $this->descriptor->mediaName(),
                    "name" => $m->name,
                    "url" => $m->url
                ];
            }
        }

        if ($additionalParams->has("start_date") && !empty($additionalParams["start_date"])
            && !$additionalParams->has("end_date")) {
            $startDate = $this->adaptDateString($additionalParams["start_date"]);
            $dt = Carbon::parse($startDate)->subDays($this->faker->randomElement([30, 60, 90, 120, 180]));
            $overrides[$activityMarkerKeyName] = new CarbonDateRange($dt, null);
        }

        if ($additionalParams->has("end_date") && !empty($additionalParams["end_date"])
            && !$additionalParams->has("start_date")) {
            $endDate = $this->adaptDateString($additionalParams["end_date"]);
            $to = Carbon::parse($endDate)->addDays($this->faker->randomElement([30, 60, 90, 120, 180]));
            $from = $to->copy()->subDays($this->faker->randomElement([30, 60, 90, 120, 180]));
            $overrides[$activityMarkerKeyName] = new CarbonDateRange($from, $to);
        }

        if ($additionalParams->has("start_date") && $additionalParams->has("end_date")
            && !empty($additionalParams["start_date"]) && !empty($additionalParams["end_date"])) {
            $originalFrom = Carbon::parse($this->adaptDateString($additionalParams["start_date"]));
            $originalTo = Carbon::parse($this->adaptDateString($additionalParams["end_date"]));
            $interval = $originalTo->diff($originalFrom);
            $afterOrBefore = $this->faker->randomElement(["after", "before"]);

            $randomDayIntervalValue = $this->faker->numberBetween(8, 90) + $interval->days;

            [$artificialFrom, $artificialTo] = match ($afterOrBefore) {
                "after" => [$originalFrom->addDays($randomDayIntervalValue), $originalTo->addDays($randomDayIntervalValue)],
                "before" => [$originalFrom->subDays($randomDayIntervalValue), $originalTo->subDays($randomDayIntervalValue)]
            };

            $overrides[$activityMarkerKeyName] = new CarbonDateRange($artificialFrom, $artificialTo);
        }

        return $overrides;
    }

    protected function ensureGenreExists(int $genreId): Model
    {
        $m = $this->descriptor->genreQueryBuilder()->firstWhere("mal_id", $genreId);
        if ($m == null) {
            $f = $this->descriptor->genreFactory();
            $m = $f->createOne([
                "mal_id" => $genreId
            ]);
        }

        return $m;
    }

    protected function adaptDateString($dateStr): string
    {
        $parts = explode("-", $dateStr);
        if (count($parts) === 1) {
            return $parts[0] . "-01-01";
        }

        return $dateStr;
    }

    protected function isScoreValueValid($score): bool
    {
        return $score <= 9.99 && $score >= 0.0;
    }

    protected function getRandomRating(): string
    {
        return $this->faker->randomElement([
            "G - All Ages",
            "PG - Children",
            "PG-13 - Teens 13 or older",
            "R - 17+ (violence & profanity)",
            "R+ - Mild Nudity",
            "Rx - Hentai"
        ]);
    }
}
