<?php

namespace App\Mcp\Tools;

use App\Enums\AnimeStatusEnum;
use App\Enums\AnimeTypeEnum;
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

#[Name('search_anime')]
#[Description('Search the Jikan v4 anime catalog by title. Returns at most 25 compact anime cards (the default is 10) with optional type, status and minimum-score filters. Read-only.')]
#[IsReadOnly]
#[IsIdempotent]
final class SearchAnimeTool extends Tool
{
    /**
     * Fields kept from each /v4/anime entry to keep the tool output bounded.
     *
     * @var array<int, string>
     */
    private const CARD_FIELDS = [
        'mal_id',
        'url',
        'images',
        'title',
        'title_english',
        'title_japanese',
        'type',
        'source',
        'episodes',
        'status',
        'airing',
        'duration',
        'rating',
        'score',
        'scored_by',
        'rank',
        'popularity',
        'members',
        'favorites',
        'season',
        'year',
    ];

    private const DEFAULT_LIMIT = 10;

    private const MAX_LIMIT = 25;

    public function __construct(
        private readonly V4ApiClient $api,
    ) {}

    public function handle(Request $request): Response|ResponseFactory
    {
        $validated = $request->validate([
            'query' => ['required', 'string', 'min:1', 'max:100'],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:'.self::MAX_LIMIT],
            'type' => ['sometimes', 'string', Rule::in(array_keys(AnimeTypeEnum::toArray()))],
            'status' => ['sometimes', 'string', Rule::in(array_keys(AnimeStatusEnum::toArray()))],
            'min_score' => ['sometimes', 'numeric', 'between:0,10'],
        ], [
            'query.required' => 'Provide a search query in `query` (1-100 characters).',
            'query.min' => 'The `query` must not be empty. Use at least one character.',
            'query.max' => 'The `query` must be 100 characters or fewer.',
            'limit.min' => 'The `limit` must be at least 1.',
            'limit.max' => 'The `limit` must be at most 25.',
            'type.in' => 'The `type` must be one of: tv, movie, ova, special, ona, music, cm, pv, tv_special.',
            'status.in' => 'The `status` must be one of: airing, complete, upcoming.',
            'min_score.between' => 'The `min_score` must be between 0 and 10.',
        ]);

        $params = [
            'q' => $validated['query'],
            'limit' => $validated['limit'] ?? self::DEFAULT_LIMIT,
        ];

        foreach (['type', 'status', 'min_score'] as $filter) {
            if (array_key_exists($filter, $validated) && $validated[$filter] !== null) {
                $params[$filter] = $validated[$filter];
            }
        }

        $result = $this->api->get('/v4/anime', $params);

        if ($result['status'] !== 200) {
            return Response::error(V4ApiClient::errorMessage($result, 'Unable to search the v4 anime catalog.'));
        }

        $data = data_get($result['body'], 'data');

        if (!is_array($data)) {
            return Response::error('Unexpected response from the v4 anime catalog.');
        }

        $items = array_map(
            fn (array $anime): array => $this->card($anime),
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
            'query' => $schema->string()
                ->description('Title keywords to search for (1-100 characters).')
                ->min(1)
                ->max(100)
                ->required(),

            'limit' => $schema->integer()
                ->description('Maximum number of cards to return (1-25).')
                ->min(1)
                ->max(self::MAX_LIMIT)
                ->default(self::DEFAULT_LIMIT),

            'type' => $schema->string()
                ->description('Filter by anime type.')
                ->enum(array_keys(AnimeTypeEnum::toArray())),

            'status' => $schema->string()
                ->description('Filter by airing status.')
                ->enum(array_keys(AnimeStatusEnum::toArray())),

            'min_score' => $schema->number()
                ->description('Only return entries scored at or above this value (0-10).')
                ->min(0)
                ->max(10),
        ];
    }

    /**
     * @param  array<string, mixed>  $anime
     * @return array<string, mixed>
     */
    private function card(array $anime): array
    {
        $card = [];

        foreach (self::CARD_FIELDS as $field) {
            if (array_key_exists($field, $anime)) {
                $card[$field] = $anime[$field];
            }
        }

        return $card;
    }
}
