<?php

namespace App\Mcp\Tools;

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

#[Name('get_anime_relations')]
#[Description('Fetch the related entries (sequels, adaptations, side stories, ...) of an anime from the Jikan v4 API by its MyAnimeList id. Each relation list is capped at 50 entries and flags truncation. Read-only.')]
#[IsReadOnly]
#[IsIdempotent]
final class GetAnimeRelationsTool extends Tool
{
    private const MAX_ENTRIES_PER_RELATION = 50;

    public function __construct(
        private readonly V4ApiClient $api,
    ) {}

    public function handle(Request $request): Response|ResponseFactory
    {
        $validated = $request->validate([
            'mal_id' => ['required', 'integer', 'min:1'],
        ], [
            'mal_id.required' => 'Provide a MyAnimeList id in `mal_id` (a positive integer).',
            'mal_id.integer' => 'The `mal_id` must be a positive integer.',
            'mal_id.min' => 'The `mal_id` must be at least 1.',
        ]);

        $malId = (int) $validated['mal_id'];
        $result = $this->api->get('/v4/anime/'.$malId.'/relations');

        if ($result['status'] === 404) {
            return Response::error("No anime found with mal_id {$malId}.");
        }

        if ($result['status'] !== 200) {
            return Response::error(V4ApiClient::errorMessage($result, "Unable to fetch relations for anime with mal_id {$malId}."));
        }

        $data = data_get($result['body'], 'data');

        if (!is_array($data)) {
            return Response::error('Unexpected response from the v4 anime relations endpoint.');
        }

        $relations = [];
        $truncated = false;

        foreach ($data as $group) {
            if (!is_array($group)) {
                continue;
            }

            $entries = $group['entry'] ?? [];
            $entries = is_array($entries) ? array_values(array_filter($entries, 'is_array')) : [];
            $total = count($entries);
            $groupTruncated = $total > self::MAX_ENTRIES_PER_RELATION;
            $truncated = $truncated || $groupTruncated;

            $relations[] = [
                'relation' => is_string($group['relation'] ?? null) ? $group['relation'] : null,
                'entry' => array_slice($entries, 0, self::MAX_ENTRIES_PER_RELATION),
                'entry_total' => $total,
                'truncated' => $groupTruncated,
            ];
        }

        return Response::structured([
            'data' => $relations,
            'truncated' => $truncated,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'mal_id' => $schema->integer()
                ->description('MyAnimeList id of the anime (for example 1 for Cowboy Bebop).')
                ->min(1)
                ->required(),
        ];
    }
}
