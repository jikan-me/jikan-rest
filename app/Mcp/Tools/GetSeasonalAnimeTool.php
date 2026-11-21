<?php

namespace App\Mcp\Tools;

use App\Enums\AnimeSeasonEnum;
use App\Mcp\Support\CompactCard;
use App\Mcp\Support\V4ApiClient;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Validation\Rule;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Name('get_seasonal_anime')]
#[Description('List the anime that premiered in a given year and season from the Jikan v4 API. Returns at most 25 compact anime cards (the default is 10). Read-only.')]
#[IsReadOnly]
#[IsIdempotent]
final class GetSeasonalAnimeTool extends Tool
{
    private const DEFAULT_LIMIT = 10;

    private const MAX_LIMIT = 25;

    public function __construct(
        private readonly V4ApiClient $api,
    ) {}

    public function handle(Request $request): Response|ResponseFactory
    {
        $validated = $request->validate([
            'year' => ['required', 'integer', 'between:1000,2999'],
            'season' => ['required', 'string', Rule::in(array_keys(AnimeSeasonEnum::toArray()))],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:'.self::MAX_LIMIT],
        ], [
            'year.required' => 'Provide the season year in `year` (for example 2024).',
            'year.integer' => 'The `year` must be an integer (four digits, for example 2024).',
            'year.between' => 'The `year` must be between 1000 and 2999.',
            'season.required' => 'Provide a season in `season`.',
            'season.in' => 'The `season` must be one of: winter, spring, summer, fall.',
            'limit.min' => 'The `limit` must be at least 1.',
            'limit.max' => 'The `limit` must be at most 25.',
        ]);

        $year = (int) $validated['year'];
        $season = (string) $validated['season'];

        $result = $this->api->get("/v4/seasons/{$year}/{$season}", [
            'limit' => $validated['limit'] ?? self::DEFAULT_LIMIT,
        ]);

        if ($result['status'] !== 200) {
            return Response::error(V4ApiClient::errorMessage($result, "Unable to fetch the {$season} {$year} season."));
        }

        $data = data_get($result['body'], 'data');

        if (!is_array($data)) {
            return Response::error('Unexpected response from the v4 seasonal endpoint.');
        }

        $items = array_map(
            fn (array $anime): array => CompactCard::anime($anime),
            array_values(array_filter($data, 'is_array')),
        );

        return Response::structured([
            'count' => count($items),
            'has_next_page' => (bool) data_get($result['body'], 'pagination.has_next_page', false),
            'items' => $items,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'year' => $schema->integer()
                ->description('Four-digit year of the season (1000-2999), for example 2024.')
                ->min(1000)
                ->max(2999)
                ->required(),

            'season' => $schema->string()
                ->description('Season of the year.')
                ->enum(array_keys(AnimeSeasonEnum::toArray()))
                ->required(),

            'limit' => $schema->integer()
                ->description('Maximum number of cards to return (1-25).')
                ->min(1)
                ->max(self::MAX_LIMIT)
                ->default(self::DEFAULT_LIMIT),
        ];
    }
}
