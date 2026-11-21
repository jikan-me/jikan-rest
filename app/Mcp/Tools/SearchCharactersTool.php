<?php

namespace App\Mcp\Tools;

use App\Mcp\Support\CompactCard;
use App\Mcp\Support\V4ApiClient;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Name('search_characters')]
#[Description('Search the Jikan v4 character catalog by name. Returns at most 25 compact character cards (the default is 10) with id, names, nicknames and favorites. Read-only.')]
#[IsReadOnly]
#[IsIdempotent]
final class SearchCharactersTool extends Tool
{
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
        ], [
            'query.required' => 'Provide a search query in `query` (1-100 characters).',
            'query.min' => 'The `query` must not be empty. Use at least one character.',
            'query.max' => 'The `query` must be 100 characters or fewer.',
            'limit.min' => 'The `limit` must be at least 1.',
            'limit.max' => 'The `limit` must be at most 25.',
        ]);

        $result = $this->api->get('/v4/characters', [
            'q' => $validated['query'],
            'limit' => $validated['limit'] ?? self::DEFAULT_LIMIT,
        ]);

        if ($result['status'] !== 200) {
            return Response::error(V4ApiClient::errorMessage($result, 'Unable to search the v4 character catalog.'));
        }

        $data = data_get($result['body'], 'data');

        if (!is_array($data)) {
            return Response::error('Unexpected response from the v4 character catalog.');
        }

        $items = array_map(
            fn (array $character): array => CompactCard::character($character),
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
                ->description('Character name keywords to search for (1-100 characters).')
                ->min(1)
                ->max(100)
                ->required(),

            'limit' => $schema->integer()
                ->description('Maximum number of cards to return (1-25).')
                ->min(1)
                ->max(self::MAX_LIMIT)
                ->default(self::DEFAULT_LIMIT),
        ];
    }
}
